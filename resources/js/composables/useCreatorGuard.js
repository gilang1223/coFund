import { ref } from 'vue';

/**
 * Shared singleton state for the creator-only modal.
 * Using module-level ref so all components share the same instance.
 */
const showCreatorModal = ref(false);

export function useCreatorGuard() {
    function openCreatorModal() {
        showCreatorModal.value = true;
    }

    function closeCreatorModal() {
        showCreatorModal.value = false;
    }

    /**
     * Navigate to profile page (where user can submit a creator request).
     */
    function goToProfile(router) {
        showCreatorModal.value = false;
        router.push('/profile');
    }

    return {
        showCreatorModal,
        openCreatorModal,
        closeCreatorModal,
        goToProfile,
    };
}
