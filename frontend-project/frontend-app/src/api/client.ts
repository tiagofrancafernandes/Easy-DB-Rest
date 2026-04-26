/**
 * Standardized API client using native fetch.
 * Follows vue-architecture and specific project rules.
 */

const BASE_URL = import.meta.env.VITE_API_URL || '/api';

export interface ApiRequestOptions extends RequestInit {
    params?: Record<string, string>;
}

export async function apiClient<T>(endpoint: string, options: ApiRequestOptions = {}): Promise<T> {
    if (!endpoint) {
        throw new Error('Endpoint is required');
    }

    const token = localStorage.getItem('easy_db_token');

    const headers = new Headers({
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...options.headers,
    });

    let url = `${BASE_URL}${endpoint}`;

    if (options.params) {
        const query = new URLSearchParams(options.params).toString();
        url += `?${query}`;
    }

    const response = await fetch(url, {
        ...options,
        headers,
    });

    // Handle 401 Unauthorized - Auto Cleanup as per RULES.md
    if (response.status === 401) {
        localStorage.removeItem('easy_db_token');
        // Optional: redirect to login if not already there
        if (window.location.pathname !== '/login') {
            window.location.href = '/login';
        }
        throw new Error('Unauthorized');
    }

    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `Request failed with status ${response.status}`);
    }

    return response.json();
}
