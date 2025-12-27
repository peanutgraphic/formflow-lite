import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { Database, RefreshCw, Download, Upload, CheckCircle2, AlertCircle, HardDrive, Bug } from 'lucide-react';
import { Card, CardHeader, Button, InfoPanel, DangerZone, DangerAction } from '../components/common';
import { endpoints } from '../api/endpoints';
import { useToast } from '../components/common/Toast';

export default function Tools() {
  const [runningTool, setRunningTool] = useState<string | null>(null);
  const { toast } = useToast();

  const runTool = useMutation({
    mutationFn: async ({ tool, action }: { tool: string; action: () => Promise<unknown> }) => {
      setRunningTool(tool);
      return action();
    },
    onSuccess: (_, { tool }) => {
      toast({ type: 'success', message: `${tool} completed successfully` });
      setRunningTool(null);
    },
    onError: (_, { tool }) => {
      toast({ type: 'error', message: `${tool} failed` });
      setRunningTool(null);
    },
  });

  const tools = [
    {
      id: 'cache',
      name: 'Clear Cache',
      description: 'Clear form and submission cache to refresh data',
      icon: RefreshCw,
      action: () => endpoints.tools.clearCache(),
    },
    {
      id: 'repair',
      name: 'Repair Database',
      description: 'Check and repair database tables',
      icon: Database,
      action: () => endpoints.tools.repairDatabase(),
    },
    {
      id: 'optimize',
      name: 'Optimize Tables',
      description: 'Optimize database tables for better performance',
      icon: HardDrive,
      action: () => endpoints.tools.optimizeTables(),
    },
    {
      id: 'export',
      name: 'Export All Data',
      description: 'Download all forms and submissions as JSON',
      icon: Download,
      action: async () => {
        const blob = await endpoints.tools.exportAll();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `formflow-export-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
      },
    },
  ];

  const diagnostics = [
    { label: 'PHP Version', value: '8.2.0', status: 'ok' },
    { label: 'WordPress Version', value: '6.4.2', status: 'ok' },
    { label: 'Plugin Version', value: '3.1.26', status: 'ok' },
    { label: 'Database Tables', value: 'All present', status: 'ok' },
    { label: 'REST API', value: 'Enabled', status: 'ok' },
    { label: 'Cron Status', value: 'Active', status: 'ok' },
    { label: 'Memory Limit', value: '256M', status: 'ok' },
    { label: 'Max Upload Size', value: '64M', status: 'ok' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Tools</h1>
        <p className="text-slate-600 dark:text-slate-400">Maintenance and diagnostic tools</p>
      </div>

      {/* Quick Tools */}
      <Card>
        <CardHeader title="Maintenance Tools" subtitle="Common maintenance actions" />
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          {tools.map((tool) => (
            <button
              key={tool.id}
              onClick={() => runTool.mutate({ tool: tool.name, action: tool.action })}
              disabled={runningTool !== null}
              className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-left disabled:opacity-50"
            >
              <div className="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                {runningTool === tool.name ? (
                  <RefreshCw className="w-5 h-5 text-teal-600 dark:text-teal-400 animate-spin" />
                ) : (
                  <tool.icon className="w-5 h-5 text-teal-600 dark:text-teal-400" />
                )}
              </div>
              <div>
                <h3 className="font-medium text-slate-900 dark:text-white">{tool.name}</h3>
                <p className="text-sm text-slate-500 dark:text-slate-400">{tool.description}</p>
              </div>
            </button>
          ))}
        </div>
      </Card>

      {/* System Diagnostics */}
      <Card>
        <CardHeader title="System Diagnostics" subtitle="Current system status" />
        <div className="mt-4 divide-y divide-slate-200 dark:divide-slate-700">
          {diagnostics.map((item, index) => (
            <div key={index} className="flex items-center justify-between py-3">
              <span className="text-slate-600 dark:text-slate-400">{item.label}</span>
              <div className="flex items-center gap-2">
                <span className="text-slate-900 dark:text-white font-medium">{item.value}</span>
                {item.status === 'ok' ? (
                  <CheckCircle2 className="w-4 h-4 text-green-500" />
                ) : item.status === 'warning' ? (
                  <AlertCircle className="w-4 h-4 text-amber-500" />
                ) : (
                  <AlertCircle className="w-4 h-4 text-red-500" />
                )}
              </div>
            </div>
          ))}
        </div>
      </Card>

      {/* Debug Info */}
      <Card>
        <CardHeader title="Debug Information" subtitle="Copy for support requests" />
        <div className="mt-4">
          <div className="bg-slate-100 dark:bg-slate-800 rounded-lg p-4 font-mono text-sm text-slate-700 dark:text-slate-300 max-h-48 overflow-auto">
            <pre>{`FormFlow Lite v3.1.26
WordPress: 6.4.2
PHP: 8.2.0
MySQL: 8.0.35
Server: Apache/2.4
Memory Limit: 256M
Max Execution Time: 300s
Upload Max Size: 64M
Active Plugins: 12
Theme: Starter Theme 1.0.0
Site URL: ${window.location.origin}
REST Prefix: /wp-json/fffl/v1/`}</pre>
          </div>
          <Button
            variant="secondary"
            size="sm"
            className="mt-3"
            onClick={() => {
              navigator.clipboard.writeText(`FormFlow Lite Debug Info\n${diagnostics.map((d) => `${d.label}: ${d.value}`).join('\n')}`);
              toast({ type: 'success', message: 'Debug info copied' });
            }}
          >
            <Bug className="w-4 h-4 mr-2" />
            Copy Debug Info
          </Button>
        </div>
      </Card>

      {/* Import Section */}
      <Card>
        <CardHeader title="Import Data" subtitle="Restore from a previous export" />
        <div className="mt-4">
          <InfoPanel variant="warning" title="Before Importing">
            Importing will merge data with existing records. Duplicate entries may be created if IDs conflict.
          </InfoPanel>
          <div className="mt-4">
            <label className="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
              <div className="flex flex-col items-center justify-center">
                <Upload className="w-8 h-8 text-slate-400 mb-2" />
                <p className="text-sm text-slate-600 dark:text-slate-400">
                  <span className="font-medium text-teal-600 dark:text-teal-400">Click to upload</span> or drag and drop
                </p>
                <p className="text-xs text-slate-500 dark:text-slate-500">JSON file from previous export</p>
              </div>
              <input
                type="file"
                className="hidden"
                accept=".json"
                onChange={async (e) => {
                  const file = e.target.files?.[0];
                  if (file) {
                    try {
                      const text = await file.text();
                      const data = JSON.parse(text);
                      await endpoints.tools.import(data);
                      toast({ type: 'success', message: 'Data imported successfully' });
                    } catch {
                      toast({ type: 'error', message: 'Failed to import data' });
                    }
                  }
                }}
              />
            </label>
          </div>
        </div>
      </Card>

      {/* Danger Zone */}
      <DangerZone>
        <DangerAction
          title="Reset All Settings"
          description="Reset all plugin settings to defaults. Forms and submissions are preserved."
          buttonLabel="Reset Settings"
          confirmMessage="This will reset all FormFlow settings to their defaults. Your forms and submissions will not be affected. Are you sure?"
          onAction={async () => { await endpoints.tools.resetSettings(); }}
        />
        <DangerAction
          title="Delete All Data"
          description="Permanently delete all forms, submissions, and settings. This cannot be undone."
          buttonLabel="Delete Everything"
          confirmMessage="WARNING: This will permanently delete ALL forms, submissions, webhooks, connectors, and settings. This action CANNOT be undone. Are you absolutely sure?"
          onAction={async () => { await endpoints.tools.deleteAll(); }}
        />
      </DangerZone>
    </div>
  );
}
