#!/bin/bash
cd /d/laragon/www/coFund || exit 1

# Kill existing servers
kill $(fuser 8000/tcp 2>/dev/null) 2>/dev/null
kill $(fuser 5173/tcp 2>/dev/null) 2>/dev/null
sleep 1

# Start Laravel in production mode (uses built assets from public/build/)
export APP_ENV=production
export APP_DEBUG=false
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel.log 2>&1 &
echo "Laravel PID: $!"

# Start Vite dev server (for local development, accessible via host)
nohup npm run dev > /tmp/vite.log 2>&1 &
echo "Vite PID: $!"

echo "Waiting for servers to start..."
sleep 3
echo "Laravel status:"
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000
echo "Vite status:"
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:5173 2>/dev/null || echo "Vite not ready yet"
