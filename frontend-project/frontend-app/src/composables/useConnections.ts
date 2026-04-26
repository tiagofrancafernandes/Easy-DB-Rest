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
}

const connections = ref<Connection[]>([]);
const currentConnection = ref<Connection | null>(null);
const isLoading = ref(false);

export function useConnections() {
    async function fetchConnections() {
        isLoading.value = true;
        try {
            // In a real app: connections.value = await apiClient<Connection[]>('/connections')
            // For now, using mock data matching DashboardView
            connections.value = [
                {
                    id: '1',
                    name: 'billing-microservice-db',
                    driver: 'postgres',
                    host: 'localhost',
                    port: 5432,
                    database: 'billing',
                    username: 'admin',
                },
                {
                    id: '2',
                    name: 'auth-production-db',
                    driver: 'mysql',
                    host: 'localhost',
                    port: 3306,
                    database: 'auth',
                    username: 'root',
                },
            ];
        } finally {
            isLoading.value = false;
        }
    }

    function setCurrentConnection(connection: Connection) {
        currentConnection.value = connection;
    }

    return {
        connections,
        currentConnection,
        isLoading,
        fetchConnections,
        setCurrentConnection,
    };
}
