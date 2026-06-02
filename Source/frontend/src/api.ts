const API_URL = import.meta.env.VITE_API_URL || '/api';

export type AuthUser = {
  id: number;
  email: string;
  fullName: string;
  role: 'admin' | 'guest';
};

export type RegistryRecord = {
  id: number;
  name: string;
  email: string;
  age: number | null;
};

function getToken() {
  return localStorage.getItem('accessToken');
}

export async function apiRequest<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const headers = new Headers(options.headers);
  headers.set('Content-Type', 'application/json');

  const token = getToken();
  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }

  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(data.message || data.error || `HTTP ${response.status}`);
  }

  return data as T;
}

export function login(email: string, password: string) {
  return apiRequest<{ accessToken: string; user: AuthUser }>('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });
}

export function fetchUsers() {
  return apiRequest<RegistryRecord[]>('/users');
}

export function createUser(payload: {
  name: string;
  email: string;
  age?: number;
}) {
  return apiRequest<RegistryRecord>('/users', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function deleteUser(id: number) {
  return apiRequest<{ success: boolean }>(`/users/${id}`, {
    method: 'DELETE',
  });
}

export function exportReport() {
  return apiRequest<{
    title: string;
    exportedAt: string;
    total: number;
    records: RegistryRecord[];
  }>('/reports/export');
}
