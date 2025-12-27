import { useState, type ReactNode } from 'react';
import { AlertTriangle, ChevronDown, ChevronUp } from 'lucide-react';
import Button from './Button';
import { ConfirmModal } from './Modal';

interface DangerZoneProps {
  title?: string;
  description?: string;
  children: ReactNode;
  defaultCollapsed?: boolean;
}

export function DangerZone({ title = 'Danger Zone', description = 'These actions are destructive and cannot be undone.', children, defaultCollapsed = true }: DangerZoneProps) {
  const [isCollapsed, setIsCollapsed] = useState(defaultCollapsed);

  return (
    <div className="border border-red-200 rounded-lg overflow-hidden">
      <button onClick={() => setIsCollapsed(!isCollapsed)} className="w-full flex items-center justify-between px-4 py-3 bg-red-50 hover:bg-red-100 transition-colors">
        <div className="flex items-center gap-3">
          <AlertTriangle className="w-5 h-5 text-red-500" />
          <div className="text-left">
            <h4 className="font-medium text-red-700">{title}</h4>
            <p className="text-sm text-red-600">{description}</p>
          </div>
        </div>
        {isCollapsed ? <ChevronDown className="w-5 h-5 text-red-500" /> : <ChevronUp className="w-5 h-5 text-red-500" />}
      </button>
      {!isCollapsed && <div className="p-4 border-t border-red-200 bg-red-50/50 space-y-4">{children}</div>}
    </div>
  );
}

interface DangerActionProps {
  title: string;
  description: string;
  buttonLabel: string;
  confirmMessage?: string;
  onAction: () => void | Promise<void>;
  loading?: boolean;
}

export function DangerAction({ title, description, buttonLabel, confirmMessage, onAction, loading }: DangerActionProps) {
  const [showConfirm, setShowConfirm] = useState(false);

  const handleConfirm = async () => {
    await onAction();
    setShowConfirm(false);
  };

  return (
    <>
      <div className="flex items-center justify-between p-4 bg-white rounded-lg border border-red-200">
        <div>
          <h5 className="font-medium text-red-700">{title}</h5>
          <p className="text-sm text-red-600">{description}</p>
        </div>
        <Button variant="danger" size="sm" onClick={() => setShowConfirm(true)} disabled={loading}>{buttonLabel}</Button>
      </div>
      <ConfirmModal
        isOpen={showConfirm}
        onClose={() => setShowConfirm(false)}
        onConfirm={handleConfirm}
        title={title}
        message={confirmMessage || `Are you sure you want to ${title.toLowerCase()}? This action cannot be undone.`}
        confirmLabel={buttonLabel}
        confirmVariant="danger"
        loading={loading}
      />
    </>
  );
}
