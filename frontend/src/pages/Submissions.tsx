import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Search, Download, Trash2, Eye, Database, ChevronLeft, ChevronRight } from 'lucide-react';
import { Card, Button, Input, Badge, Modal, ConfirmModal, SkeletonTable } from '../components/common';
import { endpoints } from '../api/endpoints';
import { format } from 'date-fns';
import { useToast } from '../components/common/Toast';
import type { Submission, FormInstance } from '../types';

export default function Submissions() {
  const [search, setSearch] = useState('');
  const [formFilter, setFormFilter] = useState<number | 'all'>('all');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [selectedSubmission, setSelectedSubmission] = useState<Submission | null>(null);
  const [showViewModal, setShowViewModal] = useState(false);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [page, setPage] = useState(1);
  const perPage = 20;
  const queryClient = useQueryClient();
  const { toast } = useToast();

  const { data: forms } = useQuery<FormInstance[]>({
    queryKey: ['forms'],
    queryFn: () => endpoints.forms.list(),
  });

  const { data: submissions, isLoading } = useQuery<Submission[]>({
    queryKey: ['submissions', { form: formFilter, status: statusFilter, page }],
    queryFn: () => endpoints.submissions.list({
      form_id: formFilter === 'all' ? undefined : formFilter,
      status: statusFilter === 'all' ? undefined : statusFilter,
      page,
      limit: perPage,
    }),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => endpoints.submissions.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['submissions'] });
      toast({ type: 'success', message: 'Submission deleted' });
      setShowDeleteConfirm(false);
      setSelectedSubmission(null);
    },
    onError: () => {
      toast({ type: 'error', message: 'Failed to delete submission' });
    },
  });

  const exportSubmissions = async () => {
    try {
      const blob = await endpoints.submissions.export({
        form_id: formFilter === 'all' ? undefined : formFilter,
        format: 'csv',
      });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `submissions-${format(new Date(), 'yyyy-MM-dd')}.csv`;
      a.click();
      URL.revokeObjectURL(url);
      toast({ type: 'success', message: 'Export downloaded' });
    } catch {
      toast({ type: 'error', message: 'Failed to export submissions' });
    }
  };

  const filteredSubmissions = submissions?.filter((sub) => {
    if (!search) return true;
    const searchLower = search.toLowerCase();
    const dataStr = JSON.stringify(sub.data).toLowerCase();
    return dataStr.includes(searchLower) || sub.form_name?.toLowerCase().includes(searchLower);
  });

  const getStatusBadge = (status: string) => {
    const variants: Record<string, 'success' | 'warning' | 'danger' | 'secondary'> = {
      completed: 'success',
      pending: 'warning',
      failed: 'danger',
      processing: 'secondary',
    };
    return <Badge variant={variants[status] || 'secondary'}>{status}</Badge>;
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Submissions</h1>
            <p className="text-slate-600 dark:text-slate-400">View and manage form submissions</p>
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
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Submissions</h1>
          <p className="text-slate-600 dark:text-slate-400">View and manage form submissions</p>
        </div>
        <Button variant="secondary" onClick={exportSubmissions}>
          <Download className="w-4 h-4 mr-2" />
          Export CSV
        </Button>
      </div>

      {/* Filters */}
      <Card className="p-4">
        <div className="flex flex-col lg:flex-row gap-4">
          <div className="flex-1">
            <Input
              placeholder="Search submissions..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              icon={<Search className="w-4 h-4" />}
            />
          </div>
          <div className="flex gap-3">
            <select
              value={formFilter}
              onChange={(e) => {
                setFormFilter(e.target.value === 'all' ? 'all' : Number(e.target.value));
                setPage(1);
              }}
              className="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
              <option value="all">All Forms</option>
              {forms?.map((form) => (
                <option key={form.id} value={form.id}>{form.name}</option>
              ))}
            </select>
            <select
              value={statusFilter}
              onChange={(e) => {
                setStatusFilter(e.target.value);
                setPage(1);
              }}
              className="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
          </div>
        </div>
      </Card>

      {/* Submissions Table */}
      <Card>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-slate-200 dark:border-slate-700">
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">ID</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Form</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Source</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Submitted</th>
                <th className="text-right py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredSubmissions?.map((submission) => (
                <tr key={submission.id} className="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                  <td className="py-3 px-4 font-mono text-sm text-slate-600 dark:text-slate-400">
                    #{submission.id}
                  </td>
                  <td className="py-3 px-4">
                    <span className="font-medium text-slate-900 dark:text-white">
                      {submission.form_name || `Form #${submission.form_id}`}
                    </span>
                  </td>
                  <td className="py-3 px-4">{getStatusBadge(submission.status)}</td>
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                    {submission.source || 'Direct'}
                  </td>
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                    {format(new Date(submission.created_at), 'MMM d, yyyy h:mm a')}
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center justify-end gap-2">
                      <button
                        onClick={() => {
                          setSelectedSubmission(submission);
                          setShowViewModal(true);
                        }}
                        className="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        title="View details"
                      >
                        <Eye className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => {
                          setSelectedSubmission(submission);
                          setShowDeleteConfirm(true);
                        }}
                        className="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        title="Delete"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {(!filteredSubmissions || filteredSubmissions.length === 0) && (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-500 dark:text-slate-400">
                    <Database className="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p className="font-medium">No submissions found</p>
                    <p className="text-sm mt-1">Submissions will appear here when forms are filled out</p>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {submissions && submissions.length >= perPage && (
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
                disabled={submissions.length < perPage}
              >
                <ChevronRight className="w-4 h-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>

      {/* View Submission Modal */}
      <Modal
        isOpen={showViewModal}
        onClose={() => {
          setShowViewModal(false);
          setSelectedSubmission(null);
        }}
        title={`Submission #${selectedSubmission?.id}`}
        size="lg"
      >
        {selectedSubmission && (
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="text-sm font-medium text-slate-500 dark:text-slate-400">Form</label>
                <p className="text-slate-900 dark:text-white">{selectedSubmission.form_name || `Form #${selectedSubmission.form_id}`}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-slate-500 dark:text-slate-400">Status</label>
                <div className="mt-1">{getStatusBadge(selectedSubmission.status)}</div>
              </div>
              <div>
                <label className="text-sm font-medium text-slate-500 dark:text-slate-400">Submitted</label>
                <p className="text-slate-900 dark:text-white">{format(new Date(selectedSubmission.created_at), 'PPpp')}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-slate-500 dark:text-slate-400">Source</label>
                <p className="text-slate-900 dark:text-white">{selectedSubmission.source || 'Direct'}</p>
              </div>
            </div>

            <div>
              <label className="text-sm font-medium text-slate-500 dark:text-slate-400">Submission Data</label>
              <div className="mt-2 bg-slate-100 dark:bg-slate-800 rounded-lg p-4 overflow-auto max-h-64">
                <pre className="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">
                  {JSON.stringify(selectedSubmission.data, null, 2)}
                </pre>
              </div>
            </div>

            {selectedSubmission.utm && Object.keys(selectedSubmission.utm).length > 0 && (
              <div>
                <label className="text-sm font-medium text-slate-500 dark:text-slate-400">UTM Parameters</label>
                <div className="mt-2 flex flex-wrap gap-2">
                  {Object.entries(selectedSubmission.utm).map(([key, value]) => (
                    <Badge key={key} variant="secondary">
                      {key}: {String(value)}
                    </Badge>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}
      </Modal>

      {/* Delete Confirmation */}
      <ConfirmModal
        isOpen={showDeleteConfirm}
        onClose={() => {
          setShowDeleteConfirm(false);
          setSelectedSubmission(null);
        }}
        onConfirm={() => selectedSubmission && deleteMutation.mutate(selectedSubmission.id)}
        title="Delete Submission"
        message="Are you sure you want to delete this submission? This action cannot be undone."
        confirmLabel="Delete"
        confirmVariant="danger"
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
