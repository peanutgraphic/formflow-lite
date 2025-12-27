import { apiFetch } from './client';
import type {
  FormInstance,
  Submission,
  ScheduleSlot,
  ScheduleSettings,
  Webhook,
  AnalyticsData,
  ActivityLog,
  Connector,
  DashboardStats,
  Settings,
} from '../types';

// Dashboard
export const dashboard = {
  getStats: () =>
    apiFetch<DashboardStats>('/dashboard/stats'),
};

// Forms
export const forms = {
  list: (params?: { page?: number; per_page?: number; status?: string; search?: string }) =>
    apiFetch<FormInstance[]>('/forms', { params }),

  get: (id: number) =>
    apiFetch<FormInstance>(`/forms/${id}`),

  create: (data: Partial<FormInstance>) =>
    apiFetch<FormInstance>('/forms', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  update: (id: number, data: Partial<FormInstance>) =>
    apiFetch<FormInstance>(`/forms/${id}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),

  delete: (id: number) =>
    apiFetch<{ success: boolean }>(`/forms/${id}`, {
      method: 'DELETE',
    }),

  duplicate: (id: number) =>
    apiFetch<FormInstance>(`/forms/${id}/duplicate`, {
      method: 'POST',
    }),

  getSchema: (id: number) =>
    apiFetch<Record<string, unknown>>(`/builder/schema/${id}`),

  saveSchema: (id: number, schema: Record<string, unknown>) =>
    apiFetch<{ success: boolean }>(`/builder/schema/${id}`, {
      method: 'POST',
      body: JSON.stringify({ schema }),
    }),
};

// Submissions
export const submissions = {
  list: (params?: {
    page?: number;
    limit?: number;
    per_page?: number;
    form_id?: number;
    instance_id?: number;
    status?: string;
    search?: string;
    date_from?: string;
    date_to?: string;
  }) =>
    apiFetch<Submission[]>('/submissions', { params }),

  get: (id: number) =>
    apiFetch<Submission>(`/submissions/${id}`),

  delete: (id: number) =>
    apiFetch<{ success: boolean }>(`/submissions/${id}`, {
      method: 'DELETE',
    }),

  retry: (id: number) =>
    apiFetch<Submission>(`/submissions/${id}/retry`, {
      method: 'POST',
    }),

  export: async (params?: { form_id?: number; instance_id?: number; format?: 'csv' | 'json' }): Promise<Blob> => {
    const queryParams = new URLSearchParams();
    if (params?.form_id) queryParams.set('form_id', String(params.form_id));
    if (params?.format) queryParams.set('format', params.format);
    const response = await fetch(`/wp-json/fffl/v1/submissions/export?${queryParams.toString()}`);
    return response.blob();
  },
};

// Scheduling
export const scheduling = {
  list: () =>
    apiFetch<ScheduleSlot[]>('/scheduling'),

  getSettings: (instanceId: number) =>
    apiFetch<ScheduleSettings>(`/scheduling/${instanceId}/settings`),

  updateSettings: (instanceId: number, settings: Partial<ScheduleSettings>) =>
    apiFetch<ScheduleSettings>(`/scheduling/${instanceId}/settings`, {
      method: 'PATCH',
      body: JSON.stringify(settings),
    }),

  getSlots: (instanceId: number, params?: { date_from?: string; date_to?: string }) =>
    apiFetch<ScheduleSlot[]>(`/scheduling/${instanceId}/slots`, { params }),

  create: (data: Partial<ScheduleSlot>) =>
    apiFetch<ScheduleSlot>('/scheduling', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  createSlot: (instanceId: number, slot: Partial<ScheduleSlot>) =>
    apiFetch<ScheduleSlot>(`/scheduling/${instanceId}/slots`, {
      method: 'POST',
      body: JSON.stringify(slot),
    }),

  update: (id: number, data: Partial<ScheduleSlot>) =>
    apiFetch<ScheduleSlot>(`/scheduling/${id}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),

  delete: (id: number) =>
    apiFetch<{ success: boolean }>(`/scheduling/${id}`, {
      method: 'DELETE',
    }),

  deleteSlot: (instanceId: number, slotId: number) =>
    apiFetch<{ success: boolean }>(`/scheduling/${instanceId}/slots/${slotId}`, {
      method: 'DELETE',
    }),

  blockDate: (instanceId: number, date: string) =>
    apiFetch<{ success: boolean }>(`/scheduling/${instanceId}/block`, {
      method: 'POST',
      body: JSON.stringify({ date }),
    }),
};

// Webhooks
export const webhooks = {
  list: () =>
    apiFetch<Webhook[]>('/webhooks'),

  create: (data: Partial<Webhook>) =>
    apiFetch<Webhook>('/webhooks', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  update: (id: number, data: Partial<Webhook>) =>
    apiFetch<Webhook>(`/webhooks/${id}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),

  delete: (id: number) =>
    apiFetch<{ success: boolean }>(`/webhooks/${id}`, {
      method: 'DELETE',
    }),

  test: (id: number) =>
    apiFetch<{ success: boolean; response?: unknown }>(`/webhooks/${id}/test`, {
      method: 'POST',
    }),
};

// Analytics
export const analytics = {
  overview: () =>
    apiFetch<AnalyticsData>('/analytics/overview'),

  getData: (params?: { instance_id?: number; date_from?: string; date_to?: string }) =>
    apiFetch<AnalyticsData>('/analytics', { params }),

  getAttribution: (params?: { date_from?: string; date_to?: string }) =>
    apiFetch<Record<string, unknown>>('/analytics/attribution', { params }),
};

// Activity logs
export const logs = {
  list: (params?: {
    page?: number;
    limit?: number;
    per_page?: number;
    level?: string;
    instance_id?: number;
  }) =>
    apiFetch<ActivityLog[]>('/logs', { params }),

  clear: () =>
    apiFetch<{ success: boolean }>('/logs', {
      method: 'DELETE',
    }),

  export: async (params?: { format?: 'csv' | 'json' }): Promise<Blob> => {
    const queryParams = new URLSearchParams();
    if (params?.format) queryParams.set('format', params.format);
    const response = await fetch(`/wp-json/fffl/v1/logs/export?${queryParams.toString()}`);
    return response.blob();
  },
};

// Settings
export const settings = {
  get: () =>
    apiFetch<Settings>('/settings'),

  update: (data: Partial<Settings>) =>
    apiFetch<Settings>('/settings', {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),

  clearCache: () =>
    apiFetch<{ success: boolean }>('/settings/cache', {
      method: 'DELETE',
    }),

  runDiagnostics: () =>
    apiFetch<Record<string, unknown>>('/settings/diagnostics'),
};

// Connectors
export const connectors = {
  list: () =>
    apiFetch<Connector[]>('/connectors'),

  get: (id: number) =>
    apiFetch<Connector>(`/connectors/${id}`),

  create: (data: Partial<Connector>) =>
    apiFetch<Connector>('/connectors', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  update: (id: number, data: Partial<Connector>) =>
    apiFetch<Connector>(`/connectors/${id}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    }),

  delete: (id: number) =>
    apiFetch<{ success: boolean }>(`/connectors/${id}`, {
      method: 'DELETE',
    }),

  configure: (id: number, config: Record<string, unknown>) =>
    apiFetch<{ success: boolean }>(`/connectors/${id}/configure`, {
      method: 'POST',
      body: JSON.stringify(config),
    }),

  test: (id: number) =>
    apiFetch<{ success: boolean; message?: string }>(`/connectors/${id}/test`, {
      method: 'POST',
    }),
};

// Tools
export const tools = {
  clearCache: () =>
    apiFetch<{ success: boolean }>('/tools/clear-cache', { method: 'POST' }),

  repairDatabase: () =>
    apiFetch<{ success: boolean }>('/tools/repair-database', { method: 'POST' }),

  optimizeTables: () =>
    apiFetch<{ success: boolean }>('/tools/optimize-tables', { method: 'POST' }),

  exportAll: async (): Promise<Blob> => {
    const response = await fetch('/wp-json/fffl/v1/tools/export');
    return response.blob();
  },

  import: (data: Record<string, unknown>) =>
    apiFetch<{ success: boolean }>('/tools/import', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  resetSettings: () =>
    apiFetch<{ success: boolean }>('/tools/reset-settings', { method: 'POST' }),

  deleteAll: () =>
    apiFetch<{ success: boolean }>('/tools/delete-all', { method: 'POST' }),
};

// Consolidated endpoints export
export const endpoints = {
  dashboard,
  forms,
  submissions,
  scheduling,
  webhooks,
  analytics,
  logs,
  settings,
  connectors,
  tools,
};
