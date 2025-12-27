import type { InputHTMLAttributes, ReactNode } from 'react';

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helpText?: string;
  icon?: ReactNode;
  leftIcon?: ReactNode;
}

export default function Input({ label, error, helpText, icon, leftIcon, className = '', ...props }: InputProps) {
  const displayIcon = icon || leftIcon;

  return (
    <div className="w-full">
      {label && (
        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
          {label}
          {props.required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <div className="relative">
        {displayIcon && (
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            {displayIcon}
          </div>
        )}
        <input
          className={`
            w-full rounded-lg border transition-colors
            ${displayIcon ? 'pl-10' : 'px-3'} py-2
            bg-white dark:bg-slate-800
            ${error
              ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
              : 'border-slate-300 dark:border-slate-600 focus:border-teal-500 focus:ring-teal-500'}
            focus:outline-none focus:ring-2 focus:ring-offset-0
            disabled:bg-slate-50 dark:disabled:bg-slate-900 disabled:text-slate-500
            text-slate-900 dark:text-white placeholder-slate-400
            ${className}
          `}
          {...props}
        />
      </div>
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
      {helpText && !error && <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{helpText}</p>}
    </div>
  );
}
