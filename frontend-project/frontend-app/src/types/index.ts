export interface User {
    id: string;
    name: string;
    email: string;
    avatar_url?: string;
}

export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
}

export interface QueryResult {
    columns: string[];
    rows: any[];
    execution_time: number;
}
