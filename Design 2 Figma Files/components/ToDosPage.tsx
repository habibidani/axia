import { Upload, X } from 'lucide-react';
import { useState } from 'react';

interface ToDo {
  id: string;
  text: string;
}

interface ToDosPageProps {
  todos: ToDo[];
  onUpdate: (todos: ToDo[]) => void;
  onAnalyze: () => void;
}

export function ToDosPage({ todos, onUpdate, onAnalyze }: ToDosPageProps) {
  const [bulkInput, setBulkInput] = useState('');

  const handleBulkAdd = () => {
    if (!bulkInput.trim()) return;
    
    const lines = bulkInput
      .split('\n')
      .map((line) => line.trim())
      .filter((line) => line);
    
    const newTodos: ToDo[] = lines.map((line) => ({
      id: Date.now().toString() + Math.random(),
      text: line,
    }));
    
    onUpdate([...todos, ...newTodos]);
    setBulkInput('');
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      const text = event.target?.result as string;
      const lines = text
        .split(/[\r\n]+/)
        .map((line) => line.trim())
        .filter((line) => line);
      
      const newTodos: ToDo[] = lines.map((line) => ({
        id: Date.now().toString() + Math.random(),
        text: line,
      }));
      
      onUpdate([...todos, ...newTodos]);
    };
    reader.readAsText(file);
    e.target.value = '';
  };

  const updateTodo = (id: string, text: string) => {
    onUpdate(todos.map((t) => (t.id === id ? { ...t, text } : t)));
  };

  const deleteTodo = (id: string) => {
    onUpdate(todos.filter((t) => t.id !== id));
  };

  return (
    <div className="max-w-3xl mx-auto px-6 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-[var(--text-primary)] mb-2">Your To-Dos</h1>
        <p>Paste, type, or upload your current tasks.</p>
      </div>

      {/* Main Card */}
      <div className="bg-[var(--bg-secondary)] rounded-2xl p-8 space-y-8">
        {/* Bulk Input */}
        <div className="space-y-3">
          <label className="block text-sm text-[var(--text-primary)]">Paste To-Dos (one per line)</label>
          <textarea
            value={bulkInput}
            onChange={(e) => setBulkInput(e.target.value)}
            placeholder="Write blog post&#10;Update landing page&#10;Review analytics"
            rows={6}
            className="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[#E94B8C]/50 resize-none"
          />
          {bulkInput.trim() && (
            <button
              onClick={handleBulkAdd}
              className="px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[#2A2D30] border border-[var(--border)] rounded-lg text-[var(--text-primary)] transition-colors"
            >
              Add All
            </button>
          )}
        </div>

        <div className="h-px bg-[var(--border)]" />

        {/* File Upload */}
        <div>
          <label className="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[#2A2D30] border border-[var(--border)] rounded-lg text-[var(--text-primary)] transition-colors">
            <Upload className="w-4 h-4" />
            Upload CSV
            <input
              type="file"
              accept=".csv,.txt"
              onChange={handleFileUpload}
              className="hidden"
            />
          </label>
        </div>

        {todos.length > 0 && (
          <>
            <div className="h-px bg-[var(--border)]" />

            {/* To-Do List */}
            <div className="space-y-2">
              <label className="block text-sm text-[var(--text-primary)] mb-3">Current To-Dos</label>
              {todos.map((todo) => (
                <div
                  key={todo.id}
                  className="flex items-center gap-3 p-3 bg-[var(--bg-tertiary)] hover:bg-[#2A2D30] rounded-lg group transition-colors"
                >
                  <input
                    type="text"
                    value={todo.text}
                    onChange={(e) => updateTodo(todo.id, e.target.value)}
                    placeholder="Enter task"
                    className="flex-1 bg-transparent text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none"
                  />
                  <button
                    onClick={() => deleteTodo(todo.id)}
                    className="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors opacity-0 group-hover:opacity-100"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>
              ))}
            </div>
          </>
        )}

        {/* Analyze Button */}
        {todos.filter((t) => t.text.trim()).length > 0 && (
          <div className="flex justify-center pt-6">
            <button
              onClick={onAnalyze}
              className="px-12 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
            >
              Start Analysis
            </button>
          </div>
        )}
      </div>
    </div>
  );
}