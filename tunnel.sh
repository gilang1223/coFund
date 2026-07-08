#!/bin/bash

# ============================================================
# CoFund Tunnel Manager
# ============================================================
# Usage:
#   ./tunnel.sh start     — Start Laravel + Cloudflare Tunnel
#   ./tunnel.sh stop      — Stop Laravel + Tunnel
#   ./tunnel.sh restart   — Restart (stop + start)
#   ./tunnel.sh status    — Check status servers & tunnel
#   ./tunnel.sh url       — Show last tunnel URL
# ============================================================

PROJECT_DIR="/d/laragon/www/coFund"
LARAVEL_PORT=8000
CLOUDFLARE_BIN="/d/ngrok/cloudflared"
LARAVEL_LOG="/tmp/laravel.log"
TUNNEL_LOG="/tmp/cloudflare-tunnel.log"
TUNNEL_URL_FILE="/tmp/cloudflare-tunnel-url.txt"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

# ──────────────────────────────────────────────────────────
# Windows-compatible helpers
# ──────────────────────────────────────────────────────────

# Find PID listening on a TCP port (Windows Git Bash compatible)
pid_on_port() {
    local port=$1
    # Try netstat first (works in Git Bash on Windows)
    local pid
    pid=$(netstat -ano 2>/dev/null | grep ":$port " | grep "LISTENING" | awk '{print $5}' | head -1)
    if [ -z "$pid" ]; then
        # Fallback: try fuser if available (Linux/Mac)
        pid=$(fuser "$port/tcp" 2>/dev/null)
    fi
    echo "$pid"
}

# Check if a process with PID is running
is_pid_alive() {
    local pid=$1
    kill -0 "$pid" 2>/dev/null
    return $?
}

# ──────────────────────────────────────────────────────────
# Functions
# ──────────────────────────────────────────────────────────

print_banner() {
    echo ""
    echo -e "${CYAN}╔══════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║       CoFund Tunnel Manager         ║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════╝${NC}"
    echo ""
}

check_dependencies() {
    if [ ! -f "$CLOUDFLARE_BIN" ]; then
        echo -e "${RED}ERROR: cloudflared tidak ditemukan di $CLOUDFLARE_BIN${NC}"
        echo -e "${YELLOW}Pastikan cloudflared sudah di-download dan ada di folder /d/ngrok/${NC}"
        echo -e "${YELLOW}Download: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/${NC}"
        exit 1
    fi
    if [ ! -d "$PROJECT_DIR" ]; then
        echo -e "${RED}ERROR: Project directory tidak ditemukan di $PROJECT_DIR${NC}"
        exit 1
    fi
}

kill_process_on_port() {
    local port=$1
    local pid
    pid=$(pid_on_port "$port")

    if [ -n "$pid" ] && [ "$pid" != "0" ]; then
        # Try graceful kill first
        kill "$pid" 2>/dev/null
        sleep 1

        # Force kill if still alive
        if is_pid_alive "$pid" 2>/dev/null; then
            kill -9 "$pid" 2>/dev/null
        fi
    fi

    # Windows fallback: taskkill
    taskkill //F //IM "php.exe" //FI "WINDOWTITLE eq *artisan serve*" 2>/dev/null
}

kill_cloudflared() {
    # Kill by process name (primary method)
    taskkill //F //IM cloudflared.exe 2>/dev/null

    # Also try by PID
    local pids
    pids=$(ps aux 2>/dev/null | grep cloudflared | grep -v grep | awk '{print $2}')
    if [ -n "$pids" ]; then
        kill $pids 2>/dev/null
        sleep 1
        local remaining
        remaining=$(ps aux 2>/dev/null | grep cloudflared | grep -v grep | awk '{print $2}')
        if [ -n "$remaining" ]; then
            kill -9 $remaining 2>/dev/null
        fi
    fi
}

# ──────────────────────────────────────────────────────────
# Commands
# ──────────────────────────────────────────────────────────

cmd_start() {
    print_banner
    check_dependencies

    echo -e "${YELLOW}[1/4] Membersihkan proses lama...${NC}"
    kill_process_on_port $LARAVEL_PORT
    kill_cloudflared
    sleep 1

    cd "$PROJECT_DIR" || exit 1

    echo -e "${YELLOW}[2/4] Menjalankan Laravel server...${NC}"
    export APP_ENV=production
    export APP_DEBUG=false
    nohup php artisan serve --host=0.0.0.0 --port=$LARAVEL_PORT > "$LARAVEL_LOG" 2>&1 &
    LARAVEL_PID=$!
    echo -e "  └─ PID: ${GREEN}$LARAVEL_PID${NC}"

    # Tunggu Laravel siap (max 15 detik)
    sleep 2
    LARAVEL_OK=false
    for i in {1..13}; do
        status=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:$LARAVEL_PORT 2>/dev/null)
        if [ "$status" = "200" ]; then
            echo -e "  └─ Status: ${GREEN}OK (HTTP $status)${NC}"
            LARAVEL_OK=true
            break
        fi
        sleep 1
    done

    if [ "$LARAVEL_OK" = false ]; then
        echo -e "  └─ Status: ${RED}Gagal start${NC}"
        echo -e "  └─ ${YELLOW}Log:${NC}"
        tail -10 "$LARAVEL_LOG" 2>/dev/null | sed 's/^/     /'
        echo ""
        echo -e "${RED}❌ Laravel gagal start. Perbaiki error di atas, lalu coba lagi.${NC}"
        exit 1
    fi

    echo -e "${YELLOW}[3/4] Menjalankan Cloudflare Tunnel...${NC}"
    rm -f "$TUNNEL_URL_FILE"

    nohup "$CLOUDFLARE_BIN" tunnel --url http://localhost:$LARAVEL_PORT > "$TUNNEL_LOG" 2>&1 &
    TUNNEL_PID=$!
    echo -e "  └─ PID: ${GREEN}$TUNNEL_PID${NC}"

    echo -e "${YELLOW}[4/4] Menunggu tunnel siap (30 detik)...${NC}"
    TUNNEL_URL=""
    for i in {1..30}; do
        # Gunakan grep -oE sebagai ganti -oP (lebih kompatibel)
        TUNNEL_URL=$(grep -oE 'https://[a-z-]+\.trycloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | head -1)
        if [ -n "$TUNNEL_URL" ]; then
            echo "$TUNNEL_URL" > "$TUNNEL_URL_FILE"
            break
        fi
        # Loading animation
        if [ $((i % 5)) -eq 0 ]; then
            echo -e "  └─ Menunggu... (${i}s)"
        fi
        sleep 1
    done

    echo ""
    if [ -n "$TUNNEL_URL" ]; then
        echo -e "${GREEN}╔══════════════════════════════════════╗${NC}"
        echo -e "${GREEN}║  ✅  TUNNEL AKTIF!                    ║${NC}"
        echo -e "${GREEN}╠══════════════════════════════════════╣${NC}"
        echo -e "${GREEN}║${NC}"
        echo -e "${GREEN}║  ${CYAN}$TUNNEL_URL${NC}"
        echo -e "${GREEN}║${NC}"
        echo -e "${GREEN}╚══════════════════════════════════════╝${NC}"
    else
        echo -e "${RED}⚠️  Tunnel URL belum ditemukan dalam 30 detik.${NC}"
        echo -e "${YELLOW}  Kemungkinan tunnel masih connecting.${NC}"
        echo -e ""
        echo -e "${YELLOW}  Cek log tunnel:${NC}"
        echo -e "  ${CYAN}tail -30 $TUNNEL_LOG${NC}"
        echo ""
        echo -e "${YELLOW}  Nanti cek URL dengan:${NC}"
        echo -e "  ${CYAN}./tunnel.sh url${NC}"
    fi

    echo ""
    echo -e "${YELLOW}Proses berjalan:${NC}"
    echo -e "  Laravel  : ${GREEN}http://localhost:$LARAVEL_PORT${NC}"
    echo -e "  Tunnel   : ${GREEN}$(cat "$TUNNEL_URL_FILE" 2>/dev/null || echo 'loading...')${NC}"
    echo ""
    echo -e "${YELLOW}Untuk stop:${NC}"
    echo -e "  ${CYAN}./tunnel.sh stop${NC}"
    echo ""
}

cmd_stop() {
    print_banner

    echo -e "${YELLOW}Menghentikan semua proses...${NC}"

    echo -e "  ├─ Menghentikan Cloudflare Tunnel..."
    kill_cloudflared
    echo -e "  │  └─ ${GREEN}Stopped${NC}"

    echo -e "  ├─ Menghentikan Laravel server..."
    kill_process_on_port $LARAVEL_PORT
    echo -e "  │  └─ ${GREEN}Stopped${NC}"

    rm -f "$TUNNEL_URL_FILE"

    echo ""
    echo -e "${GREEN}✅ Semua proses dihentikan.${NC}"
    echo ""
}

cmd_restart() {
    cmd_stop
    sleep 2
    cmd_start
}

cmd_status() {
    print_banner

    echo -e "${YELLOW}Status Proses:${NC}"
    echo ""

    # Cek Laravel
    laravel_pid=$(pid_on_port $LARAVEL_PORT)
    if [ -n "$laravel_pid" ] && [ "$laravel_pid" != "0" ]; then
        laravel_status=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:$LARAVEL_PORT 2>/dev/null)
        echo -e "  Laravel Server : ${GREEN}RUNNING${NC} (PID: $laravel_pid, HTTP $laravel_status)"
        echo -e "  └─ http://localhost:$LARAVEL_PORT"
    else
        echo -e "  Laravel Server : ${RED}STOPPED${NC}"
    fi

    # Cek Cloudflare Tunnel
    tunnel_pid=$(ps aux 2>/dev/null | grep cloudflared | grep -v grep | awk '{print $2}' | head -1)
    if [ -n "$tunnel_pid" ]; then
        tunnel_url=$(cat "$TUNNEL_URL_FILE" 2>/dev/null)
        if [ -n "$tunnel_url" ]; then
            echo -e "  Cloudflare Tunl: ${GREEN}RUNNING${NC} (PID: $tunnel_pid)"
            echo -e "  └─ ${CYAN}$tunnel_url${NC}"
        else
            echo -e "  Cloudflare Tunl: ${YELLOW}CONNECTING${NC} (PID: $tunnel_pid)"
            echo -e "  └─ URL belum tersedia — jalankan: ${CYAN}./tunnel.sh url${NC}"
        fi
    else
        echo -e "  Cloudflare Tunl: ${RED}STOPPED${NC}"
    fi

    echo ""

    # Cek log errors
    if [ -f "$LARAVEL_LOG" ]; then
        errors=$(tail -10 "$LARAVEL_LOG" 2>/dev/null | grep -iE "error|exception|fatal" | head -5)
        if [ -n "$errors" ]; then
            echo -e "${RED}⚠️  Ada error di Laravel log:${NC}"
            echo -e "$errors" | sed 's/^/  /'
            echo ""
        fi
    fi
    if [ -f "$TUNNEL_LOG" ]; then
        errors=$(tail -10 "$TUNNEL_LOG" 2>/dev/null | grep -iE "error|failed|panic" | head -3)
        if [ -n "$errors" ]; then
            echo -e "${RED}⚠️  Ada error di Tunnel log:${NC}"
            echo -e "$errors" | sed 's/^/  /'
            echo ""
        fi
    fi
}

cmd_url() {
    if [ -f "$TUNNEL_URL_FILE" ]; then
        url=$(cat "$TUNNEL_URL_FILE")
        echo ""
        echo -e "${CYAN}Tunnel URL:${NC}"
        echo -e "${GREEN}$url${NC}"
        echo ""
    else
        # Coba baca langsung dari log
        url=$(grep -oE 'https://[a-z-]+\.trycloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | head -1)
        if [ -n "$url" ]; then
            echo "$url" > "$TUNNEL_URL_FILE"
            echo ""
            echo -e "${CYAN}Tunnel URL:${NC}"
            echo -e "${GREEN}$url${NC}"
            echo ""
        else
            echo ""
            echo -e "${RED}Tunnel belum aktif atau URL belum tersedia.${NC}"
            echo -e "${YELLOW}Coba jalankan:${NC} ${CYAN}./tunnel.sh start${NC}"
            echo ""
        fi
    fi
}

# ──────────────────────────────────────────────────────────
# Main
# ──────────────────────────────────────────────────────────

case "${1:-help}" in
    start)
        cmd_start
        ;;
    stop)
        cmd_stop
        ;;
    restart)
        cmd_restart
        ;;
    status)
        cmd_status
        ;;
    url)
        cmd_url
        ;;
    *)
        echo ""
        echo -e "${CYAN}╔══════════════════════════════════════╗${NC}"
        echo -e "${CYAN}║         CoFund Tunnel Manager       ║${NC}"
        echo -e "${CYAN}╚══════════════════════════════════════╝${NC}"
        echo ""
        echo -e "${CYAN}Penggunaan:${NC}"
        echo ""
        echo -e "  ${GREEN}./tunnel.sh start${NC}     — Start Laravel + Cloudflare Tunnel"
        echo -e "  ${GREEN}./tunnel.sh stop${NC}      — Stop semua proses"
        echo -e "  ${GREEN}./tunnel.sh restart${NC}   — Restart"
        echo -e "  ${GREEN}./tunnel.sh status${NC}    — Cek status"
        echo -e "  ${GREEN}./tunnel.sh url${NC}       — Lihat URL tunnel"
        echo ""
        echo -e "${YELLOW}Contoh:${NC}"
        echo -e "  ${CYAN}./tunnel.sh start${NC}   → Start semuanya, dapat URL"
        echo -e "  ${CYAN}./tunnel.sh stop${NC}    → Matikan server + tunnel"
        echo -e "  ${CYAN}./tunnel.sh restart${NC} → Stop lalu start lagi (dapat URL baru)"
        echo ""
        ;;
esac
