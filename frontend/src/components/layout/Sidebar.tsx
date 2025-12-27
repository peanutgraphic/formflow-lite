import { NavLink, useLocation } from 'react-router-dom';
import { LayoutDashboard, FileText, Database, Calendar, Webhook, Settings, BarChart3, Activity, Wrench, Link2 } from 'lucide-react';

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

export default function Sidebar() {
  const location = useLocation();

  return (
    <aside className="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col">
      <div className="p-4 border-b border-slate-200 dark:border-slate-700">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg flex items-center justify-center">
            <FileText className="w-5 h-5 text-white" />
          </div>
          <div>
            <h1 className="font-bold text-slate-900 dark:text-white">FormFlow</h1>
            <p className="text-xs text-slate-500 dark:text-slate-400">Lite Edition</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 p-3 space-y-1 overflow-y-auto">
        {navigation.map((item) => {
          const isActive = location.pathname === item.href ||
            (item.href !== '/' && location.pathname.startsWith(item.href));

          return (
            <NavLink
              key={item.name}
              to={item.href}
              className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                isActive
                  ? 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'
                  : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700'
              }`}
            >
              <item.icon className={`w-5 h-5 ${isActive ? 'text-teal-600 dark:text-teal-400' : ''}`} />
              {item.name}
            </NavLink>
          );
        })}
      </nav>

      <div className="p-4 border-t border-slate-200 dark:border-slate-700">
        <div className="text-xs text-slate-500 dark:text-slate-400">
          FormFlow Lite v3.1.26
        </div>
      </div>
    </aside>
  );
}
