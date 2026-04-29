import { ref, reactive, computed } from 'vue';
import { apiClient } from '@/api/client';
import type { QueryResult } from '@/types';

export interface Table {
    name: string;
    schema?: string;
    rows?: number;
}

export interface Column {
    name: string;
    type: string;
    nullable: boolean;
    default: string | null;
    isPk: boolean;
}

// Shared state to keep instances in sync
const tablesMap = reactive<Record<string, Table[]>>({});
const loadingMap = reactive<Record<string, boolean>>({});
const currentDatabaseMap = reactive<Record<string, string>>({});
const currentSchemaMap = reactive<Record<string, string>>({});

export function useDatabase(connectionId: string) {
    if (!tablesMap[connectionId]) {
        tablesMap[connectionId] = [];
        loadingMap[connectionId] = false;
        currentDatabaseMap[connectionId] = '';
        currentSchemaMap[connectionId] = ''; // Default to empty to avoid issues with SQLite
    }

    const tables = computed({
        get: () => tablesMap[connectionId] || [],
        set: (val) => (tablesMap[connectionId] = val),
    });

    const isLoading = computed({
        get: () => loadingMap[connectionId] || false,
        set: (val) => (loadingMap[connectionId] = val),
    });

    const currentDatabase = computed({
        get: () => currentDatabaseMap[connectionId] || '',
        set: (val) => (currentDatabaseMap[connectionId] = val),
    });

    const currentSchema = computed({
        get: () => currentSchemaMap[connectionId] || '',
        set: (val) => (currentSchemaMap[connectionId] = val),
    });

    async function executeQuery(sql: string): Promise<QueryResult> {
        isLoading.value = true;
        try {
            const params: Record<string, string> = {};
            if (currentDatabase.value) params['overrides[database]'] = currentDatabase.value;
            if (currentSchema.value) params['overrides[schema]'] = currentSchema.value;

            return await apiClient<QueryResult>('/query/raw', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/sql',
                    'X-Config-ID': connectionId,
                },
                params,
                body: sql,
            });
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchTables() {
        isLoading.value = true;
        try {
            const params: Record<string, string> = {};
            if (currentDatabase.value) params.database = currentDatabase.value;
            if (currentSchema.value) params.schema = currentSchema.value;

            const response = await apiClient<{ data: Table[] }>(`/connections/${connectionId}/tables`, { params });
            tables.value = response.data;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchTableDetails(tableName: string) {
        isLoading.value = true;
        try {
            const params: Record<string, string> = {};
            if (currentDatabase.value) params.database = currentDatabase.value;
            if (currentSchema.value) params.schema = currentSchema.value;

            return await apiClient<{ data: { columns: Column[] } }>(
                `/connections/${connectionId}/tables/${tableName}`,
                { params }
            );
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchDatabases() {
        const response = await apiClient<{ data: string[] | any[] }>(`/connections/${connectionId}/databases`);
        // Some drivers might return objects or strings
        return response.data.map((d) => (typeof d === 'string' ? d : d.name || d.Database));
    }

    async function fetchSchemas() {
        const params: Record<string, string> = {};
        if (currentDatabase.value) params.database = currentDatabase.value;
        const response = await apiClient<{ data: string[] | any[] }>(`/connections/${connectionId}/schemas`, {
            params,
        });
        return response.data.map((s) => (typeof s === 'string' ? s : s.schema_name || s.name));
    }

    function setDatabase(db: string) {
        currentDatabase.value = db;
    }

    function setSchema(schema: string) {
        currentSchema.value = schema;
    }

    async function fetchTableData(tableName: string, options: { limit?: number; offset?: number } = {}) {
        isLoading.value = true;
        try {
            const params: Record<string, any> = { ...options };
            if (currentDatabase.value) params.database = currentDatabase.value;
            if (currentSchema.value) params.schema = currentSchema.value;

            return await apiClient<{ data: any[]; meta: { total: number } }>(
                `/connections/${connectionId}/tables/${tableName}/data`,
                { params }
            );
        } finally {
            isLoading.value = false;
        }
    }

    async function insertRecord(tableName: string, data: Record<string, any>) {
        isLoading.value = true;
        try {
            const body: any = { data };
            if (currentDatabase.value) body.database = currentDatabase.value;
            if (currentSchema.value) body.schema = currentSchema.value;

            return await apiClient(`/connections/${connectionId}/tables/${tableName}/data`, {
                method: 'POST',
                body: JSON.stringify(body),
            });
        } finally {
            isLoading.value = false;
        }
    }

    async function updateRecord(tableName: string, pk: Record<string, any>, data: Record<string, any>) {
        isLoading.value = true;
        try {
            const body: any = { pk, data };
            if (currentDatabase.value) body.database = currentDatabase.value;
            if (currentSchema.value) body.schema = currentSchema.value;

            return await apiClient(`/connections/${connectionId}/tables/${tableName}/data`, {
                method: 'PUT',
                body: JSON.stringify(body),
            });
        } finally {
            isLoading.value = false;
        }
    }

    async function deleteRecord(tableName: string, pk: Record<string, any>) {
        isLoading.value = true;
        try {
            const params: Record<string, any> = { ...pk };
            if (currentDatabase.value) params.database = currentDatabase.value;
            if (currentSchema.value) params.schema = currentSchema.value;

            return await apiClient(`/connections/${connectionId}/tables/${tableName}/data`, {
                method: 'DELETE',
                params,
            });
        } finally {
            isLoading.value = false;
        }
    }

    return {
        tables,
        isLoading,
        currentDatabase,
        currentSchema,
        executeQuery,
        fetchTables,
        fetchTableDetails,
        fetchDatabases,
        fetchSchemas,
        fetchTableData,
        insertRecord,
        updateRecord,
        deleteRecord,
        setDatabase,
        setSchema,
    };
}
