import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Plus, Search, FileText, Copy, Trash2, ExternalLink, Edit, Eye, EyeOff } from 'lucide-react';
import { Card, Button, Input, Badge, Modal, ConfirmModal, SkeletonTable } from '../components/common';
import { endpoints } from '../api/endpoints';
import { format } from 'date-fns';
import { useToast } from '../components/common/Toast';
import type { FormInstance, FormStatus } from '../types';

export default function Forms() {
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState<FormStatus | 'all'>('all');
  const [selectedForm, setSelectedForm] = useState<FormInstance | null>(null);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [showEmbedModal, setShowEmbedModal] = useState(false);
  const queryClient = useQueryClient();
  const { toast } = useToast();

  const { data: forms, isLoading } = useQuery<FormInstance[]>({
    queryKey: ['forms'],
    queryFn: () => endpoints.forms.list(),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => endpoints.forms.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['forms'] });
      toast({ type: 'success', message: 'Form deleted successfully' });
      setShowDeleteConfirm(false);
      setSelectedForm(null);
    },
    onError: () => {
      toast({ type: 'error', message: 'Failed to delete form' });
    },
  });

  const updateStatusMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: FormStatus }) =>
      endpoints.forms.update(id, { status }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['forms'] });
      toast({ type: 'success', message: 'Form status updated' });
    },
    onError: () => {
      toast({ type: 'error', message: 'Failed to update status' });
    },
  });

  const filteredForms = forms?.filter((form) => {
    const matchesSearch = form.name.toLowerCase().includes(search.toLowerCase()) ||
      form.slug.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = statusFilter === 'all' || form.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const copyEmbedCode = () => {
    if (!selectedForm) return;
    const code = `[formflow id="${selectedForm.id}"]`;
    navigator.clipboard.writeText(code);
    toast({ type: 'success', message: 'Shortcode copied to clipboard' });
  };

  const getStatusBadge = (status: FormStatus) => {
    const variants: Record<FormStatus, 'success' | 'warning' | 'secondary'> = {
      active: 'success',
      draft: 'warning',
      archived: 'secondary',
    };
    return <Badge variant={variants[status]}>{status}</Badge>;
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Forms</h1>
            <p className="text-slate-600 dark:text-slate-400">Manage your form instances</p>
          </div>
        </div>
        <SkeletonTable rows={5} />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Forms</h1>
          <p className="text-slate-600 dark:text-slate-400">Manage your form instances</p>
        </div>
        <Button onClick={() => window.open('/wp-admin/admin.php?page=formflow-editor', '_blank')}>
          <Plus className="w-4 h-4 mr-2" />
          Create Form
        </Button>
      </div>

      {/* Filters */}
      <Card className="p-4">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1">
            <Input
              placeholder="Search forms..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              icon={<Search className="w-4 h-4" />}
            />
          </div>
          <div className="flex gap-2">
            {(['all', 'active', 'draft', 'archived'] as const).map((status) => (
              <button
                key={status}
                onClick={() => setStatusFilter(status)}
                className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                  statusFilter === status
                    ? 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'
                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'
                }`}
              >
                {status.charAt(0).toUpperCase() + status.slice(1)}
              </button>
            ))}
          </div>
        </div>
      </Card>

      {/* Forms Table */}
      <Card>
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-slate-200 dark:border-slate-700">
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Form</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Status</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Connector</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Submissions</th>
                <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Created</th>
                <th className="text-right py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredForms?.map((form) => (
                <tr key={form.id} className="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                  <td className="py-3 px-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                        <FileText className="w-5 h-5 text-teal-600 dark:text-teal-400" />
                      </div>
                      <div>
                        <div className="font-medium text-slate-900 dark:text-white">{form.name}</div>
                        <div className="text-sm text-slate-500 dark:text-slate-400">{form.slug}</div>
                      </div>
                    </div>
                  </td>
                  <td className="py-3 px-4">{getStatusBadge(form.status)}</td>
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                    {form.connector_id || 'None'}
                  </td>
                  <td className="py-3 px-4">
                    <span className="font-medium text-slate-900 dark:text-white">{form.submissions_count}</span>
                  </td>
                  <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">
                    {format(new Date(form.created_at), 'MMM d, yyyy')}
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center justify-end gap-2">
                      <button
                        onClick={() => {
                          setSelectedForm(form);
                          setShowEmbedModal(true);
                        }}
                        className="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        title="Get embed code"
                      >
                        <Copy className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => updateStatusMutation.mutate({
                          id: form.id,
                          status: form.status === 'active' ? 'draft' : 'active',
                        })}
                        className="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        title={form.status === 'active' ? 'Deactivate' : 'Activate'}
                      >
                        {form.status === 'active' ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                      </button>
                      <button
                        onClick={() => window.open(`/wp-admin/admin.php?page=formflow-editor&form=${form.id}`, '_blank')}
                        className="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        title="Edit form"
                      >
                        <Edit className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => {
                          setSelectedForm(form);
                          setShowDeleteConfirm(true);
                        }}
                        className="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        title="Delete form"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {(!filteredForms || filteredForms.length === 0) && (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-500 dark:text-slate-400">
                    <FileText className="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p className="font-medium">No forms found</p>
                    <p className="text-sm mt-1">Create your first form to get started</p>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </Card>

      {/* Embed Code Modal */}
      <Modal
        isOpen={showEmbedModal}
        onClose={() => {
          setShowEmbedModal(false);
          setSelectedForm(null);
        }}
        title="Embed Form"
      >
        <div className="space-y-4">
          <p className="text-sm text-slate-600 dark:text-slate-400">
            Use this shortcode to embed the form on any page or post:
          </p>
          <div className="bg-slate-100 dark:bg-slate-800 rounded-lg p-4 font-mono text-sm">
            [formflow id="{selectedForm?.id}"]
          </div>
          <div className="flex gap-3">
            <Button onClick={copyEmbedCode} className="flex-1">
              <Copy className="w-4 h-4 mr-2" />
              Copy Shortcode
            </Button>
            <Button
              variant="secondary"
              onClick={() => {
                if (selectedForm) {
                  window.open(`/?formflow_preview=${selectedForm.id}`, '_blank');
                }
              }}
            >
              <ExternalLink className="w-4 h-4 mr-2" />
              Preview
            </Button>
          </div>
        </div>
      </Modal>

      {/* Delete Confirmation */}
      <ConfirmModal
        isOpen={showDeleteConfirm}
        onClose={() => {
          setShowDeleteConfirm(false);
          setSelectedForm(null);
        }}
        onConfirm={() => selectedForm && deleteMutation.mutate(selectedForm.id)}
        title="Delete Form"
        message={`Are you sure you want to delete "${selectedForm?.name}"? This will also delete all associated submissions. This action cannot be undone.`}
        confirmLabel="Delete Form"
        confirmVariant="danger"
        loading={deleteMutation.isPending}
      />
    </div>
  );
}
