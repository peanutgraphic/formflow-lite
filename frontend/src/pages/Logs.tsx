import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Activity, Search, Trash2, ChevronLeft, ChevronRight, AlertCircle, CheckCircle2, Info, AlertTriangle, Download } from 'lucide-react';
import { Card, Button, Input, Badge, ConfirmModal, SkeletonTable } from '../components/common';
import { endpoints } from '../api/endpoints';
import { format } from 'date-fns';
import { useToast } from '../components/common/Toast';
import type { ActivityLog } from '../types';

type LogLevel = 'info' | 'warning' | 'error' | 'success';

export default function Logs() {
  const [search, setSearch] = useState('');
  const [levelFilter, setLevelFilter] = useState<LogLevel | 'all'>('all');
  const [page, setPage] = useState(1);
  const [showClearConfirm, setShowClearConfirm] = useState(false);
  const perPage = 50;
  const queryClient = useQueryClient();
  const { toast } = useToast();

  const { data: logs, isLoading } = useQuery<ActivityLog[]>({
    queryKey: ['logs', { level: levelFilter, page }],
    queryFn: () => endpoints.logs.list({
      level: levelFilter === 'all' ? undefined : levelFilter,
      page,
      limit: perPage,
    }),
  });

  const clearMutation = useMutation({
    mutationFn: () => endpoints.logs.clear(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['logs'] });
      toast({ type: 'success', message: 'Logs cleared successfully' });
      setShowClearConfirm(false);
    },
    onError: () => {
      toast({ type: 'error', message: 'Failed to clear logs' });
    },
  });

  const filteredLogs = logs?.filter((log) => {
    if (!search) return true;
    const searchLower = search.toLowerCase();
    return (
      log.message.toLowerCase().includes(searchLower) ||
      log.action.toLowerCase().includes(searchLower) ||
      log.context?.toLowerCase().includes(searchLower)
    );
  });

  const exportLogs = async () => {
    try {
      const blob = await endpoints.logs.export({ format: 'csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `formflow-logs-${format(new Date(), 'yyyy-MM-dd')}.csv`;
      a.click();
      URL.revokeObjectURL(url);
      toast({ type: 'success', message: 'Logs exported' });
    } catch {
      toast({ type: 'error', message: 'Failed to export logs' });
    }
  };

  const getLevelIcon = (level: LogLevel) => {
    const icons = {
      info: <Info className="w-4 h-4" />,
      warning: <AlertTriangle className="w-4 h-4" />,
      error: <AlertCircle className="w-4 h-4" />,
      success: <CheckCircle2 className="w-4 h-4" />,
    };
    return icons[level] || <Info className="w-4 h-4" />;
  };

  const getLevelBadge = (level: LogLevel) => {
    const variants: Record<LogLevel, 'info' | 'warning' | 'danger' | 'success'> = {
      info: 'info',
      warning: 'warning',
      error: 'danger',
      success: 'success',
    };
    return (
      <Badge variant={variants[level]} className="flex items-center gap-1">
        {getLevelIcon(level)}
        {level}
      </Badge>
    );
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Activity Logs</h1>
            <p className="text-slate-600 dark:text-slate-400">View system and form activity</p>
          </div>
        </div>
        <SkeletonTable rows={10} />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Activity Logs</h1>
          <p className="text-slate-600 dark:text-slate-400">View system and form activity</p>
        </div>
        <div className="flex gap-2">
          <Button variant="secondary" onClick={exportLogs}>
            <Download className="w-4 h-4 mr-2" />
            Export
          </Button>
          <Button variant="danger" onClick={() => setShowClearConfirm(true)}>
            <Trash2 className="w-4 h-4 mr-2" />
            Clear Logs
          </Button>
        </div>
      </div>

      {/* Filters */}
      <Card className="p-4">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1">
            <Input
              placeholder="Search logs..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              icon={<Search className="w-4 h-4" />}
            />
          </div>
          <div className="flex gap-2">
            {(['all', 'info', 'warning', 'error', 'success'] as const).map((level) => (
              <button
                key={level}
                onClick={() => {
                  setLevelFilter(level);
                  setPage(1);
                }}
                className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                  levelFilter === level
                    ? 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'
                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'
                }`}
              >
                {level.charAt(0).toUpperCase() + level.slice(1)}
              </button>
            ))}
          </div>
        </div>
      </Card>

      {/* Logs Table */}
      <Card>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-slate-200 dark:border-slate-700">
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Time</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Level</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Action</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Message</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Context</th>
              </tr>
            </thead>
            <tbody>
              {filteredLogs?.map((log) => (
                <tr key={log.id} className="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">
                    {format(new Date(log.created_at), 'MMM d, h:mm:ss a')}
                  </td>
                  <td className="py-3 px-4">
                    {getLevelBadge(log.level as LogLevel)}
                  </td>
                  <td className="py-3 px-4">
                    <code className="text-sm bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-slate-700 dark:text-slate-300">
                      {log.action}
                    </code>
                  </td>
                  <td className="py-3 px-4 text-sm text-slate-900 dark:text-white max-w-md truncate">
                    {log.message}
                  </td>
                  <td className="py-3 px-4 text-sm text-slate-500 dark:text-slate-400 max-w-xs truncate">
                    {log.context || '-'}
                  </td>
                </tr>
              ))}
              {(!filteredLogs || filteredLogs.length === 0) && (
                <tr>
                  <td colSpan={5} className="py-12 text-center text-slate-500 dark:text-slate-400">
                    <Activity className="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p className="font-medium">No logs found</p>
                    <p className="text-sm mt-1">Activity will be recorded here</p>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {logs && logs.length >= perPage && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700">
            <div className="text-sm text-slate-600 dark:text-slate-400">
              Page {page}
            </div>
            <div className="flex gap-2">
              <Button
                variant="secondary"
                size="sm"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
              >
                <ChevronLeft className="w-4 h-4" />
              </Button>
              <Button
                variant="secondary"
                size="sm"
                onClick={() => setPage((p) => p + 1)}
                disabled={logs.length < perPage}
              >
                <ChevronRight className="w-4 h-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>

      {/* Clear Confirmation */}
      <ConfirmModal
        isOpen={showClearConfirm}
        onClose={() => setShowClearConfirm(false)}
        onConfirm={() => clearMutation.mutate()}
        title="Clear All Logs"
        message="Are you sure you want to clear all activity logs? This action cannot be undone."
        confirmLabel="Clear Logs"
        confirmVariant="danger"
        loading={clearMutation.isPending}
      />
    </div>
  );
}
