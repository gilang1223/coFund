import { ref } from 'vue';
import { transactionApi } from '@/services/api';
import dayjs from 'dayjs';

export function useTransaction() {
    const transactions = ref([]);
    const currentTransaction = ref(null);
    const isLoading = ref(false);
    const error = ref(null);

    async function fetchTransactions() {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await transactionApi.getAll();
            transactions.value = response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch transactions';
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchTransaction(reference) {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await transactionApi.getByReference(reference);
            currentTransaction.value = response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch transaction';
            throw err;
        } finally {
            isLoading.value = false;
        }
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    function formatDate(date) {
        return dayjs(date).format('DD MMM YYYY HH:mm');
    }

    function getTypeLabel(type) {
        const labels = {
            payment: 'Pembayaran',
            disbursement: 'Pencairan Dana',
            refund: 'Pengembalian Dana',
            platform_fee: 'Biaya Platform',
            top_up: 'Top-Up Saldo',
            withdrawal: 'Penarikan Dana',
        };
        return labels[type] || type;
    }

    function getTypeIcon(type) {
        const icons = {
            payment: 'pi-arrow-right',
            disbursement: 'pi-arrow-up',
            refund: 'pi-arrow-left',
            platform_fee: 'pi-percentage',
            top_up: 'pi-arrow-down',
            withdrawal: 'pi-arrow-up',
        };
        return icons[type] || 'pi-question';
    }

    function getTypeColor(type) {
        const colors = {
            payment: 'text-brand-400',
            disbursement: 'text-green-400',
            refund: 'text-orange-400',
            platform_fee: 'text-purple-400',
            top_up: 'text-green-400',
            withdrawal: 'text-yellow-400',
        };
        return colors[type] || 'text-gray-400';
    }

    function getStatusBadgeClass(status) {
        const map = {
            pending: 'badge-draft',
            success: 'badge-success',
            failed: 'badge-failed',
        };
        return map[status] || 'badge-default';
    }

    return {
        transactions,
        currentTransaction,
        isLoading,
        error,
        fetchTransactions,
        fetchTransaction,
        formatCurrency,
        formatDate,
        getTypeLabel,
        getTypeIcon,
        getTypeColor,
        getStatusBadgeClass,
    };
}
