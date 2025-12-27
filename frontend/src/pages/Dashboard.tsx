import { useQuery } from '@tanstack/react-query';
import { FileText, Database, Clock, TrendingUp, Activity } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';
import { Card, CardHeader, StatCard, Badge, DashboardSkeleton } from '../components/common';
import { endpoints } from '../api/endpoints';
import { format } from 'date-fns';
import type { FormInstance, Submission, AnalyticsData } from '../types';

export default function Dashboard() {
  const { data: forms, isLoading: formsLoading } = useQuery<FormInstance[]>({
    queryKey: ['forms'],
    queryFn: () => endpoints.forms.list(),
  });

  const { data: submissions, isLoading: submissionsLoading } = useQuery<Submission[]>({
    queryKey: ['submissions', 'recent'],
    queryFn: () => endpoints.submissions.list({ limit: 10 }),
  });

  const { data: analytics, isLoading: analyticsLoading } = useQuery<AnalyticsData>({
    queryKey: ['analytics', 'overview'],
    queryFn: () => endpoints.analytics.overview(),
  });

  const isLoading = formsLoading || submissionsLoading || analyticsLoading;

  if (isLoading) {
    return <DashboardSkeleton />;
  }

  const activeForms = forms?.filter((f) => f.status === 'active').length || 0;
  const totalSubmissions = forms?.reduce((acc, f) => acc + f.submissions_count, 0) || 0;
  const todaySubmissions = analytics?.submissions_today || 0;
  const weeklyGrowth = analytics?.weekly_growth || 0;

  // Mock chart data - in production this would come from analytics API
  const submissionTrend = analytics?.daily_submissions || Array.from({ length: 7 }, (_, i) => ({
    date: format(new Date(Date.now() - (6 - i) * 86400000), 'MMM d'),
    count: Math.floor(Math.random() * 50) + 10,
  }));

  const formPerformance = forms?.slice(0, 5).map((f) => ({
    name: f.name.length > 15 ? f.name.substring(0, 15) + '...' : f.name,
    submissions: f.submissions_count,
  })) || [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
        <p className="text-slate-600 dark:text-slate-400">Overview of your form performance and submissions</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          title="Active Forms"
          value={activeForms}
          icon={<FileText className="w-5 h-5" />}
          trend={forms?.length ? { value: forms.length, label: 'total forms' } : undefined}
        />
        <StatCard
          title="Total Submissions"
          value={totalSubmissions.toLocaleString()}
          icon={<Database className="w-5 h-5" />}
        />
        <StatCard
          title="Today's Submissions"
          value={todaySubmissions}
          icon={<Clock className="w-5 h-5" />}
          trend={weeklyGrowth !== 0 ? {
            value: Math.abs(weeklyGrowth),
            label: 'vs last week',
            isPositive: weeklyGrowth > 0,
          } : undefined}
        />
        <StatCard
          title="Weekly Growth"
          value={`${weeklyGrowth > 0 ? '+' : ''}${weeklyGrowth}%`}
          icon={<TrendingUp className="w-5 h-5" />}
        />
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader title="Submission Trend" subtitle="Last 7 days" />
          <div className="h-64 mt-4">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={submissionTrend}>
                <CartesianGrid strokeDasharray="3 3" className="stroke-slate-200 dark:stroke-slate-700" />
                <XAxis dataKey="date" className="text-xs" tick={{ fill: '#64748b' }} />
                <YAxis className="text-xs" tick={{ fill: '#64748b' }} />
                <Tooltip
                  contentStyle={{
                    backgroundColor: 'var(--tooltip-bg, #fff)',
                    border: '1px solid var(--tooltip-border, #e2e8f0)',
                    borderRadius: '8px',
                  }}
                />
                <Line
                  type="monotone"
                  dataKey="count"
                  stroke="#14b8a6"
                  strokeWidth={2}
                  dot={{ fill: '#14b8a6', strokeWidth: 2 }}
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </Card>

        <Card>
          <CardHeader title="Top Forms" subtitle="By submission count" />
          <div className="h-64 mt-4">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={formPerformance} layout="vertical">
                <CartesianGrid strokeDasharray="3 3" className="stroke-slate-200 dark:stroke-slate-700" />
                <XAxis type="number" className="text-xs" tick={{ fill: '#64748b' }} />
                <YAxis type="category" dataKey="name" className="text-xs" tick={{ fill: '#64748b' }} width={100} />
                <Tooltip
                  contentStyle={{
                    backgroundColor: 'var(--tooltip-bg, #fff)',
                    border: '1px solid var(--tooltip-border, #e2e8f0)',
                    borderRadius: '8px',
                  }}
                />
                <Bar dataKey="submissions" fill="#14b8a6" radius={[0, 4, 4, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </Card>
      </div>

      {/* Recent Submissions */}
      <Card>
        <CardHeader title="Recent Submissions" subtitle="Latest form entries" />
        <div className="mt-4 overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-slate-200 dark:border-slate-700">
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Form</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Submitted</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Source</th>
              </tr>
            </thead>
            <tbody>
              {submissions?.slice(0, 5).map((submission) => (
                <tr key={submission.id} className="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                  <td className="py-3 px-4">
                    <span className="font-medium text-slate-900 dark:text-white">{submission.form_name || `Form #${submission.form_id}`}</span>
                  </td>
                  <td className="py-3 px-4">
                    <Badge variant={submission.status === 'completed' ? 'success' : submission.status === 'pending' ? 'warning' : 'secondary'}>
                      {submission.status}
                    </Badge>
                  </td>
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                    {format(new Date(submission.created_at), 'MMM d, h:mm a')}
                  </td>
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                    {submission.source || 'Direct'}
                  </td>
                </tr>
              ))}
              {(!submissions || submissions.length === 0) && (
                <tr>
                  <td colSpan={4} className="py-8 text-center text-slate-500 dark:text-slate-400">
                    <Activity className="w-8 h-8 mx-auto mb-2 opacity-50" />
                    No recent submissions
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}
