// WordPress REST API client

declare global {
  interface Window {
    formflowLite?: {
      apiUrl: string;
      nonce: string;
      adminUrl: string;
    };
  }
}

const getConfig = () => {
  if (typeof window !== 'undefined' && window.formflowLite) {
    return window.formflowLite;
  }
  return {
    apiUrl: '/wp-json/fffl/v1',
    nonce: '',
    adminUrl: '/wp-admin/',
  };
};

export interface FetchOptions extends RequestInit {
  params?: Record<string, string | number | boolean | undefined>;
}

export async function apiFetch<T>(
  endpoint: string,
  options: FetchOptions = {}
): Promise<T> {
  const config = getConfig();
  const { params, ...fetchOptions } = options;

  let url = `${config.apiUrl}${endpoint}`;

  if (params) {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined) {
        searchParams.append(key, String(value));
      }
    });
    const queryString = searchParams.toString();
    if (queryString) {
      url += `?${queryString}`;
    }
  }

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    ...(options.headers as Record<string, string>),
  };

  if (config.nonce) {
    headers['X-WP-Nonce'] = config.nonce;
  }

  const response = await fetch(url, {
    ...fetchOptions,
    headers,
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'An error occurred' }));
    throw new Error(error.message || `HTTP ${response.status}`);
  }

  return response.json();
}
