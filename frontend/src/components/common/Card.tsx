import type { ReactNode } from 'react';

interface CardProps {
  children: ReactNode;
  className?: string;
  padding?: 'none' | 'sm' | 'md' | 'lg';
}

export default function Card({ children, className = '', padding = 'md' }: CardProps) {
  const paddingClasses = { none: '', sm: 'p-3', md: 'p-4', lg: 'p-6' };

  return (
    <div className={`bg-white rounded-lg border border-slate-200 shadow-sm ${paddingClasses[padding]} ${className}`}>
      {children}
    </div>
  );
}

export interface CardHeaderProps {
  title: ReactNode;
  subtitle?: string;
  description?: string;
  action?: ReactNode;
}

export function CardHeader({ title, subtitle, description, action }: CardHeaderProps) {
  return (
    <div className="flex items-start justify-between mb-4">
      <div>
        <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{title}</h3>
        {(subtitle || description) && <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{subtitle || description}</p>}
      </div>
      {action && <div>{action}</div>}
    </div>
  );
}

export interface StatCardProps {
  title: string;
  label?: string;
  value: string | number;
  icon?: ReactNode;
  trend?: { value: number; label?: string; isPositive?: boolean; positive?: boolean };
  color?: 'blue' | 'green' | 'amber' | 'red' | 'purple' | 'teal';
}

export function StatCard({ title, label, value, icon, trend, color = 'teal' }: StatCardProps) {
  const displayLabel = title || label;
  const colors = {
    blue: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    green: 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    red: 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    purple: 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
    teal: 'bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400',
  };

  const isPositive = trend?.isPositive ?? trend?.positive;

  return (
    <Card>
      <div className="flex items-start justify-between">
        <div>
          <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{displayLabel}</p>
          <p className="text-2xl font-bold text-slate-900 dark:text-white mt-1">{value}</p>
          {trend && (
            <p className={`text-sm mt-1 flex items-center gap-1 ${isPositive ? 'text-green-600' : 'text-red-600'}`}>
              {isPositive ? '+' : ''}{trend.value}%
              {trend.label && <span className="text-slate-500 dark:text-slate-400"> {trend.label}</span>}
            </p>
          )}
        </div>
        {icon && <div className={`p-2 rounded-lg ${colors[color]}`}>{icon}</div>}
      </div>
    </Card>
  );
}
