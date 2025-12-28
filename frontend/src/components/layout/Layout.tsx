import { type ReactNode } from 'react';
import { NavLink, Outlet, useLocation } from 'react-router-dom';
import { clsx } from 'clsx';
import {
  LayoutDashboard,
  FileText,
  Database,
  Calendar,
  Webhook,
  Settings,
  BarChart3,
  Activity,
  Wrench,
  Link2,
} from 'lucide-react';

interface LayoutProps {
  children?: ReactNode;
  title?: string;
  description?: string;
  action?: ReactNode;
}

const navigation = [
  { name: 'Dashboard', href: '/', icon: LayoutDashboard },
  { name: 'Forms', href: '/forms', icon: FileText },
  { name: 'Submissions', href: '/submissions', icon: Database },
  { name: 'Analytics', href: '/analytics', icon: BarChart3 },
  { name: 'Scheduling', href: '/scheduling', icon: Calendar },
  { name: 'Webhooks', href: '/webhooks', icon: Webhook },
  { name: 'Connectors', href: '/connectors', icon: Link2 },
  { name: 'Logs', href: '/logs', icon: Activity },
  { name: 'Tools', href: '/tools', icon: Wrench },
  { name: 'Settings', href: '/settings', icon: Settings },
];

const pageTitles: Record<string, { title: string; description?: string }> = {
  '/': { title: 'Dashboard', description: 'Overview of your forms and submissions' },
  '/forms': { title: 'Forms', description: 'Manage your enrollment forms' },
  '/submissions': { title: 'Submissions', description: 'View and manage form submissions' },
  '/analytics': { title: 'Analytics', description: 'Insights and statistics' },
  '/scheduling': { title: 'Scheduling', description: 'Manage scheduling availability' },
  '/webhooks': { title: 'Webhooks', description: 'Configure webhook integrations' },
  '/connectors': { title: 'Connectors', description: 'API integrations and connectors' },
  '/logs': { title: 'Logs', description: 'Activity and error logs' },
  '/tools': { title: 'Tools', description: 'Utilities and diagnostics' },
  '/settings': { title: 'Settings', description: 'Configure plugin settings' },
};

export default function Layout({ children, title, description, action }: LayoutProps) {
  const location = useLocation();
  const pageInfo = pageTitles[location.pathname] || { title: 'FormFlow' };
  const displayTitle = title || pageInfo.title;
  const displayDescription = description || pageInfo.description;

  return (
    <div className="min-h-screen bg-slate-50">
      {/* Top Navigation */}
      <header className="bg-white border-b border-slate-200">
        <div className="px-6 py-4">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-xl font-semibold text-slate-900">{displayTitle}</h1>
              {displayDescription && (
                <p className="text-sm text-slate-500 mt-0.5">{displayDescription}</p>
              )}
            </div>
            {action && <div>{action}</div>}
          </div>
        </div>
        {/* Tab Navigation */}
        <nav className="px-6 flex gap-1 overflow-x-auto">
          {navigation.map((item) => (
            <NavLink
              key={item.name}
              to={item.href}
              className={({ isActive }) =>
                clsx(
                  'flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap transition-colors',
                  isActive
                    ? 'border-primary-600 text-primary-600'
                    : 'border-transparent text-slate-600 hover:text-slate-900 hover:border-slate-300'
                )
              }
            >
              <item.icon className="w-4 h-4" />
              {item.name}
            </NavLink>
          ))}
        </nav>
      </header>

      {/* Main Content */}
      <main className="p-6 overflow-x-hidden">
        {children || <Outlet />}
      </main>
    </div>
  );
}
