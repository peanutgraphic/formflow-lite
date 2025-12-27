import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Calendar, TrendingUp, BarChart3, PieChart as PieIcon } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, PieChart, Pie, Cell } from 'recharts';
import { Card, CardHeader, StatCard, Skeleton } from '../components/common';
import { endpoints } from '../api/endpoints';
import { format, subDays } from 'date-fns';
import type { FormInstance, AnalyticsData } from '../types';

const COLORS = ['#14b8a6', '#0ea5e9', '#8b5cf6', '#f59e0b', '#ef4444'];

export default function Analytics() {
  const [dateRange, setDateRange] = useState<'7d' | '30d' | '90d'>('30d');

  const { data: forms } = useQuery<FormInstance[]>({
    queryKey: ['forms'],
    queryFn: () => endpoints.forms.list(),
  });

  const { isLoading } = useQuery<AnalyticsData>({
    queryKey: ['analytics', dateRange],
    queryFn: () => endpoints.analytics.overview(),
  });

  // Generate mock trend data based on date range
  const days = dateRange === '7d' ? 7 : dateRange === '30d' ? 30 : 90;
  const trendData = Array.from({ length: days }, (_, i) => ({
    date: format(subDays(new Date(), days - 1 - i), dateRange === '90d' ? 'MMM d' : 'MMM d'),
    submissions: Math.floor(Math.random() * 50) + 10,
    conversions: Math.floor(Math.random() * 30) + 5,
  }));

  // Aggregate for display
  const displayData = dateRange === '90d'
    ? trendData.filter((_, i) => i % 7 === 0)
    : dateRange === '30d'
    ? trendData.filter((_, i) => i % 3 === 0)
    : trendData;

  const totalSubmissions = trendData.reduce((acc, d) => acc + d.submissions, 0);
  const totalConversions = trendData.reduce((acc, d) => acc + d.conversions, 0);
  const conversionRate = totalSubmissions > 0 ? ((totalConversions / totalSubmissions) * 100).toFixed(1) : '0';
  const avgDaily = Math.round(totalSubmissions / days);

  const formDistribution = forms?.slice(0, 5).map((form, i) => ({
    name: form.name.length > 20 ? form.name.substring(0, 20) + '...' : form.name,
    value: form.submissions_count,
    color: COLORS[i % COLORS.length],
  })) || [];

  const sourceData = [
    { source: 'Direct', count: Math.floor(totalSubmissions * 0.4) },
    { source: 'Organic', count: Math.floor(totalSubmissions * 0.25) },
    { source: 'Paid', count: Math.floor(totalSubmissions * 0.2) },
    { source: 'Email', count: Math.floor(totalSubmissions * 0.1) },
    { source: 'Social', count: Math.floor(totalSubmissions * 0.05) },
  ];

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Analytics</h1>
            <p className="text-slate-600 dark:text-slate-400">Form performance insights</p>
          </div>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          {Array.from({ length: 4 }).map((_, i) => (
            <Card key={i} className="p-4">
              <Skeleton className="h-4 w-24 mb-2" />
              <Skeleton className="h-8 w-16" />
            </Card>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Analytics</h1>
          <p className="text-slate-600 dark:text-slate-400">Form performance insights</p>
        </div>
        <div className="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
          {(['7d', '30d', '90d'] as const).map((range) => (
            <button
              key={range}
              onClick={() => setDateRange(range)}
              className={`px-3 py-1.5 text-sm font-medium rounded-md transition-colors ${
                dateRange === range
                  ? 'bg-white dark:bg-slate-700 text-teal-600 dark:text-teal-400 shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              }`}
            >
              {range === '7d' ? '7 Days' : range === '30d' ? '30 Days' : '90 Days'}
            </button>
          ))}
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          title="Total Submissions"
          value={totalSubmissions.toLocaleString()}
          icon={<BarChart3 className="w-5 h-5" />}
          trend={{ value: 12, label: 'vs previous period', isPositive: true }}
        />
        <StatCard
          title="Conversion Rate"
          value={`${conversionRate}%`}
          icon={<TrendingUp className="w-5 h-5" />}
          trend={{ value: 3.2, label: 'vs previous period', isPositive: true }}
        />
        <StatCard
          title="Avg. Daily"
          value={avgDaily}
          icon={<Calendar className="w-5 h-5" />}
        />
        <StatCard
          title="Active Forms"
          value={forms?.filter((f) => f.status === 'active').length || 0}
          icon={<PieIcon className="w-5 h-5" />}
        />
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card className="lg:col-span-2">
          <CardHeader title="Submission Trend" subtitle={`Last ${days} days`} />
          <div className="h-72 mt-4">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={displayData}>
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
                  dataKey="submissions"
                  stroke="#14b8a6"
                  strokeWidth={2}
                  dot={false}
                  name="Submissions"
                />
                <Line
                  type="monotone"
                  dataKey="conversions"
                  stroke="#8b5cf6"
                  strokeWidth={2}
                  dot={false}
                  name="Conversions"
                />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </Card>

        <Card>
          <CardHeader title="Form Distribution" subtitle="Submissions by form" />
          <div className="h-72 mt-4">
            {formDistribution.length > 0 ? (
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={formDistribution}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    paddingAngle={2}
                    dataKey="value"
                  >
                    {formDistribution.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip
                    contentStyle={{
                      backgroundColor: 'var(--tooltip-bg, #fff)',
                      border: '1px solid var(--tooltip-border, #e2e8f0)',
                      borderRadius: '8px',
                    }}
                  />
                </PieChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-full flex items-center justify-center text-slate-500 dark:text-slate-400">
                No data available
              </div>
            )}
          </div>
          {formDistribution.length > 0 && (
            <div className="mt-4 space-y-2">
              {formDistribution.map((item, i) => (
                <div key={i} className="flex items-center justify-between text-sm">
                  <div className="flex items-center gap-2">
                    <div className="w-3 h-3 rounded-full" style={{ backgroundColor: item.color }} />
                    <span className="text-slate-600 dark:text-slate-400">{item.name}</span>
                  </div>
                  <span className="font-medium text-slate-900 dark:text-white">{item.value}</span>
                </div>
              ))}
            </div>
          )}
        </Card>
      </div>

      {/* Source Breakdown */}
      <Card>
        <CardHeader title="Traffic Sources" subtitle="Where submissions come from" />
        <div className="h-64 mt-4">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={sourceData} layout="vertical">
              <CartesianGrid strokeDasharray="3 3" className="stroke-slate-200 dark:stroke-slate-700" />
              <XAxis type="number" className="text-xs" tick={{ fill: '#64748b' }} />
              <YAxis type="category" dataKey="source" className="text-xs" tick={{ fill: '#64748b' }} width={80} />
              <Tooltip
                contentStyle={{
                  backgroundColor: 'var(--tooltip-bg, #fff)',
                  border: '1px solid var(--tooltip-border, #e2e8f0)',
                  borderRadius: '8px',
                }}
              />
              <Bar dataKey="count" fill="#14b8a6" radius={[0, 4, 4, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </Card>
    </div>
  );
}
