export function LoadingOverlay() {
  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div className="text-center">
        {/* Circular Loader */}
        <div className="w-16 h-16 border-4 border-[var(--border)] border-t-white rounded-full animate-spin mx-auto mb-6" />
        
        {/* Text */}
        <div className="text-[var(--text-primary)] mb-2">Analyzing your tasks…</div>
        <div className="text-sm text-[var(--text-secondary)]">Mapping goals, impact, and priorities.</div>
      </div>
    </div>
  );
}