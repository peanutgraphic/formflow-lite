// Form instance types
export type FormStatus = 'active' | 'draft' | 'archived';

export interface FormInstance {
  id: number;
  name: string;
  slug: string;
  status: FormStatus;
  connector_id: string;
  connector_name?: string;
  form_schema?: Record<string, unknown>;
  settings: FormSettings;
  submissions_count: number;
  created_at: string;
  updated_at: string;
}

export interface FormSettings {
  success_message?: string;
  redirect_url?: string;
  enable_scheduling?: boolean;
  enable_resume?: boolean;
  require_auth?: boolean;
  branding?: {
    logo_url?: string;
    primary_color?: string;
    hide_powered_by?: boolean;
  };
}

// Submission types
export type SubmissionStatus = 'pending' | 'completed' | 'failed' | 'processing';

export interface Submission {
  id: number;
  form_id: number;
  form_name?: string;
  instance_id?: number;
  instance_name?: string;
  status: SubmissionStatus;
  data: Record<string, unknown>;
  form_data?: Record<string, unknown>;
  api_response?: Record<string, unknown>;
  error_message?: string;
  ip_address?: string;
  user_agent?: string;
  source?: string;
  utm?: Record<string, unknown>;
  utm_source?: string;
  utm_medium?: string;
  utm_campaign?: string;
  created_at: string;
  completed_at?: string;
}

// Scheduling types
export interface ScheduleSlot {
  id: number;
  form_id: number;
  instance_id?: number;
  day_of_week: number;
  date?: string;
  start_time: string;
  end_time: string;
  max_submissions: number;
  capacity?: number;
  booked?: number;
  is_active: boolean;
  is_available?: boolean;
}

export interface ScheduleSettings {
  enabled: boolean;
  slot_duration: number;
  buffer_time: number;
  advance_booking_days: number;
  max_bookings_per_slot: number;
  working_hours: {
    [day: string]: { start: string; end: string; enabled: boolean };
  };
  blocked_dates: string[];
}

// Webhook types
export interface Webhook {
  id: number;
  name: string;
  url: string;
  events: string[];
  form_id?: number;
  is_active: boolean;
  secret?: string;
  last_triggered?: string;
  last_triggered_at?: string;
  failure_count?: number;
  created_at: string;
}

// Analytics types
export interface AnalyticsData {
  submissions_today: number;
  weekly_growth: number;
  daily_submissions?: Array<{ date: string; count: number }>;
  submissions_by_day?: Array<{ date: string; count: number }>;
  submissions_by_status?: Array<{ status: string; count: number }>;
  submissions_by_source?: Array<{ source: string; count: number }>;
  top_forms?: Array<{ name: string; submissions: number }>;
  conversion_rate?: number;
  average_completion_time?: number;
}

// Activity log types
export type LogLevel = 'info' | 'warning' | 'error' | 'debug' | 'success';

export interface ActivityLog {
  id: number;
  level: LogLevel;
  action: string;
  message: string;
  context?: string;
  details?: Record<string, unknown>;
  instance_id?: number;
  submission_id?: number;
  created_at: string;
}

// Settings types
export interface PluginSettings {
  encryption_key_set: boolean;
  queue_enabled: boolean;
  cache_enabled: boolean;
  cache_ttl: number;
  debug_mode: boolean;
  retention_days: number;
}

// Connector types
export interface Connector {
  id: number;
  name: string;
  type: string;
  description?: string;
  endpoint: string;
  auth_type: 'none' | 'bearer' | 'basic' | 'api_key';
  credentials?: Record<string, string>;
  icon?: string;
  is_active: boolean;
  is_configured?: boolean;
  requires_auth?: boolean;
  config_fields?: ConnectorField[];
  last_used?: string;
  created_at?: string;
}

export interface ConnectorField {
  key: string;
  label: string;
  type: 'text' | 'password' | 'url' | 'select' | 'checkbox';
  required: boolean;
  options?: { value: string; label: string }[];
  help_text?: string;
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

// Dashboard stats
export interface DashboardStats {
  total_forms: number;
  active_forms: number;
  total_submissions: number;
  submissions_today: number;
  submissions_this_week: number;
  completion_rate: number;
  pending_queue: number;
  failed_submissions: number;
}

// Settings types
export interface Settings {
  general: {
    default_status: 'draft' | 'active';
    submissions_per_page: number;
    enable_analytics: boolean;
    enable_logging: boolean;
  };
  notifications: {
    email_notifications: boolean;
    admin_email: string;
    notify_on_submission: boolean;
    notify_on_error: boolean;
  };
  security: {
    honeypot_enabled: boolean;
    rate_limiting: boolean;
    rate_limit_per_minute: number;
    require_consent: boolean;
    consent_text: string;
  };
  styling: {
    use_theme_styles: boolean;
    custom_css: string;
  };
}
