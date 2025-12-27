import { useState, type ReactNode } from 'react';
import { Info, Lightbulb, AlertCircle, CheckCircle2, ChevronDown, ChevronUp } from 'lucide-react';

type Variant = 'info' | 'tip' | 'warning' | 'success' | 'amber';

const config = {
  info: { icon: Info, bg: 'bg-blue-50', border: 'border-l-blue-400', title: 'text-blue-700', text: 'text-blue-600', iconColor: 'text-blue-500' },
  tip: { icon: Lightbulb, bg: 'bg-amber-50', border: 'border-l-amber-400', title: 'text-amber-700', text: 'text-amber-600', iconColor: 'text-amber-500' },
  warning: { icon: AlertCircle, bg: 'bg-orange-50', border: 'border-l-orange-400', title: 'text-orange-700', text: 'text-orange-600', iconColor: 'text-orange-500' },
  success: { icon: CheckCircle2, bg: 'bg-green-50', border: 'border-l-green-400', title: 'text-green-700', text: 'text-green-600', iconColor: 'text-green-500' },
  amber: { icon: Info, bg: 'bg-amber-50', border: 'border-l-amber-400', title: 'text-amber-700', text: 'text-amber-600', iconColor: 'text-amber-500' },
};

interface CollapsibleBannerProps {
  variant?: Variant;
  title: string;
  icon?: ReactNode;
  children: ReactNode;
  defaultOpen?: boolean;
  dismissible?: boolean;
  onDismiss?: () => void;
}

export function CollapsibleBanner({ variant = 'amber', title, icon, children, defaultOpen = true, dismissible = false, onDismiss }: CollapsibleBannerProps) {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const [isDismissed, setIsDismissed] = useState(false);
  const c = config[variant];

  if (isDismissed) return null;

  return (
    <div className={`rounded-lg border-l-4 ${c.bg} ${c.border}`}>
      <div className="flex items-center justify-between px-4 py-3 cursor-pointer select-none" onClick={() => setIsOpen(!isOpen)}>
        <div className="flex items-center gap-2">
          {icon && <span className={c.iconColor}>{icon}</span>}
          <span className={`font-medium ${c.title}`}>{title}</span>
        </div>
        <div className="flex items-center gap-2">
          {dismissible && (
            <button onClick={(e) => { e.stopPropagation(); setIsDismissed(true); onDismiss?.(); }} className={`${c.iconColor} hover:opacity-70 p-1`}>
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          )}
          {!dismissible && <span className={c.iconColor}>{isOpen ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}</span>}
        </div>
      </div>
      {isOpen && <div className={`px-4 pb-4 text-sm ${c.text}`}>{children}</div>}
    </div>
  );
}

export function InfoPanel({ variant = 'info', title, children }: { variant?: Variant; title: string; children: ReactNode }) {
  const c = config[variant];
  const Icon = c.icon;

  return (
    <div className={`rounded-lg border-l-4 p-4 ${c.bg} ${c.border}`}>
      <div className="flex items-start gap-3">
        <Icon className={`w-5 h-5 ${c.iconColor} flex-shrink-0 mt-0.5`} />
        <div>
          <h4 className={`font-medium ${c.title}`}>{title}</h4>
          <div className={`mt-1 text-sm ${c.text}`}>{children}</div>
        </div>
      </div>
    </div>
  );
}
