import { HashRouter, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider } from './contexts/ThemeContext';
import { ToastProvider } from './components/common';
import { Layout } from './components/layout';
import {
  Dashboard,
  Forms,
  Submissions,
  Analytics,
  Scheduling,
  Webhooks,
  Connectors,
  Logs,
  Tools,
  Settings,
} from './pages';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider>
        <ToastProvider>
          <HashRouter>
            <Routes>
              <Route path="/" element={<Layout />}>
                <Route index element={<Dashboard />} />
                <Route path="forms" element={<Forms />} />
                <Route path="submissions" element={<Submissions />} />
                <Route path="analytics" element={<Analytics />} />
                <Route path="scheduling" element={<Scheduling />} />
                <Route path="webhooks" element={<Webhooks />} />
                <Route path="connectors" element={<Connectors />} />
                <Route path="logs" element={<Logs />} />
                <Route path="tools" element={<Tools />} />
                <Route path="settings" element={<Settings />} />
              </Route>
            </Routes>
          </HashRouter>
        </ToastProvider>
      </ThemeProvider>
    </QueryClientProvider>
  );
}
