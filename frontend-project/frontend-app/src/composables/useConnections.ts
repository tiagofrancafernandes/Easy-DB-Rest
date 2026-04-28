import { ref } from 'vue';
import { apiClient } from '@/api/client';

export interface Connection {
    id: string;
    name: string;
    driver: string;
    host: string;
    port: number;
    database: string;
    username: string;
    password?: string;
    created_at: string;
}

const connections = ref<Connection[]>([]);
const currentConnection = ref<Connection | null>(null);
const isLoading = ref(false);

export function useConnections() {
    async function fetchConnections() {
        isLoading.value = true;
        try {
            const response = await apiClient<{ data: Connection[] }>('/connections');
            connections.value = response.data;
        } catch (error) {
            console.error('Failed to fetch connections', error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchConnection(id: string) {
        isLoading.value = true;
        try {
            const response = await apiClient<{ data: Connection }>(`/connections/${id}`);
            currentConnection.value = response.data;
            return response.data;
        } catch (error) {
            console.error(`Failed to fetch connection ${id}`, error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    }

    async function createConnection(data: Partial<Connection>) {
        isLoading.value = true;
        try {
            const response = await apiClient<{ data: Connection }>('/connections', {
                method: 'POST',
                body: JSON.stringify(data),
            });
            await fetchConnections();
            return response.data;
        } finally {
            isLoading.value = false;
        }
    }

    async function updateConnection(id: string, data: Partial<Connection>) {
        isLoading.value = true;
        try {
            const response = await apiClient<{ data: Connection }>(`/connections/${id}`, {
                method: 'PUT',
                body: JSON.stringify(data),
            });
            await fetchConnections();
            return response.data;
        } finally {
            isLoading.value = false;
        }
    }

    async function deleteConnection(id: string) {
        isLoading.value = true;
        try {
            await apiClient(`/connections/${id}`, { method: 'DELETE' });
            await fetchConnections();
        } finally {
            isLoading.value = false;
        }
    }

    return {
        connections,
        currentConnection,
        isLoading,
        fetchConnections,
        fetchConnection,
        createConnection,
        updateConnection,
        deleteConnection,
    };
}
