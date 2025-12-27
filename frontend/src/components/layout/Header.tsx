import { Sun, Moon, Monitor, RefreshCw, ExternalLink } from 'lucide-react';
import { useTheme } from '../../contexts/ThemeContext';
import { Tooltip } from '../common';

export default function Header() {
  const { theme, setTheme } = useTheme();

  const themeOptions = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
  ] as const;

  return (
    <header className="h-14 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6">
      <div className="flex items-center gap-4">
        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">
          Form Management
        </h2>
      </div>

      <div className="flex items-center gap-3">
        <Tooltip content="Refresh data">
          <button
            onClick={() => window.location.reload()}
            className="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
          >
            <RefreshCw className="w-4 h-4" />
          </button>
        </Tooltip>

        <div className="flex items-center bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
          {themeOptions.map((option) => (
            <Tooltip key={option.value} content={option.label}>
              <button
                onClick={() => setTheme(option.value)}
                className={`p-1.5 rounded-md transition-colors ${
                  theme === option.value
                    ? 'bg-white dark:bg-slate-600 text-teal-600 dark:text-teal-400 shadow-sm'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
                }`}
              >
                <option.icon className="w-4 h-4" />
              </button>
            </Tooltip>
          ))}
        </div>

        <Tooltip content="View documentation">
          <a
            href="https://peanut.dev/formflow"
            target="_blank"
            rel="noopener noreferrer"
            className="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
          >
            <ExternalLink className="w-4 h-4" />
          </a>
        </Tooltip>
      </div>
    </header>
  );
}
