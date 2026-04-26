import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const token = ref(localStorage.getItem('easy_db_token'));

export function useAuth() {
    const router = useRouter();

    const isAuthenticated = computed(() => !!token.value);

    function setToken(newToken: string | null) {
        if (newToken) {
            localStorage.setItem('easy_db_token', newToken);
            token.value = newToken;
            return;
        }

        localStorage.removeItem('easy_db_token');
        token.value = null;
    }

    function logout() {
        setToken(null);
        router.push('/login');
    }

    return {
        token,
        isAuthenticated,
        setToken,
        logout,
    };
}
