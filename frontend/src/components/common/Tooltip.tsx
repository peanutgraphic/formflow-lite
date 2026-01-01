import { useState, useRef, type ReactNode } from 'react';
import { createPortal } from 'react-dom';
import { HelpCircle } from 'lucide-react';

interface TooltipProps {
  content: ReactNode;
  children: ReactNode;
  position?: 'top' | 'bottom' | 'left' | 'right';
}

export function Tooltip({ content, children, position = 'top' }: TooltipProps) {
  const [isVisible, setIsVisible] = useState(false);
  const [tooltipPosition, setTooltipPosition] = useState({ top: 0, left: 0 });
  const [actualPosition, setActualPosition] = useState(position);
  const triggerRef = useRef<HTMLDivElement>(null);

  const showTooltip = () => {
    if (triggerRef.current) {
      const rect = triggerRef.current.getBoundingClientRect();
      let bestPosition = position;

      if (position === 'top' && rect.top < 80) bestPosition = 'bottom';
      else if (position === 'bottom' && rect.bottom + 80 > window.innerHeight) bestPosition = 'top';

      setActualPosition(bestPosition);

      let top = 0, left = 0;
      switch (bestPosition) {
        case 'top': top = rect.top - 8 + window.scrollY; left = rect.left + rect.width / 2 + window.scrollX; break;
        case 'bottom': top = rect.bottom + 8 + window.scrollY; left = rect.left + rect.width / 2 + window.scrollX; break;
        case 'left': top = rect.top + rect.height / 2 + window.scrollY; left = rect.left - 8 + window.scrollX; break;
        case 'right': top = rect.top + rect.height / 2 + window.scrollY; left = rect.right + 8 + window.scrollX; break;
      }
      setTooltipPosition({ top, left });
    }
    setIsVisible(true);
  };

  const positionClasses = {
    top: '-translate-x-1/2 -translate-y-full mb-2',
    bottom: '-translate-x-1/2 mt-2',
    left: '-translate-x-full -translate-y-1/2 mr-2',
    right: '-translate-y-1/2 ml-2',
  };

  return (
    <>
      <div ref={triggerRef} className="inline-flex" onMouseEnter={showTooltip} onMouseLeave={() => setIsVisible(false)}>
        {children}
      </div>
      {isVisible && createPortal(
        <div className={`fixed z-[99999] px-3 py-2 text-sm bg-slate-900 text-white rounded-lg shadow-lg max-w-xs pointer-events-none ${positionClasses[actualPosition]}`}
          style={{ top: tooltipPosition.top, left: tooltipPosition.left }}>
          {content}
        </div>,
        document.body
      )}
    </>
  );
}

export function HelpTooltip({ content }: { content: ReactNode }) {
  return (
    <Tooltip content={content}>
      <HelpCircle className="w-4 h-4 text-slate-400 hover:text-slate-600 cursor-help" />
    </Tooltip>
  );
}
