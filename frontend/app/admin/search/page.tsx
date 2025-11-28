'use client';

import ListItemForm from "@/component/admin/ListItemForm";
import { useQuestionnaires } from "@/lib/hooks/useQuestionnaires";
import { useState } from "react";

export default function AdminSearchFormPage() {
  const [query, setQuery] = useState("");
  const { questionnaires, isLoading, error } = useQuestionnaires();
  const [deletedIds, setDeletedIds] = useState<number[]>([]);

  const filtered = questionnaires.filter((f) =>
    f.title.toLowerCase().includes(query.toLowerCase().trim()),
  ).filter((f) => !deletedIds.includes(f.id));

  const handleDelete = async (id: number) => {
    const res = await fetch(`/api/questionnaires/${id}`, {
      method: "DELETE",
    });
    if(!res.ok){
      return;
    }
    setDeletedIds((prev) => [...prev, id]);
  }
  return (
    <>
      <div className="border-b border-base-300 bg-base-100 px-8 py-4 shrink-0">
        <h2 className="text-2xl font-bold text-center">
          Recherche d&apos;un formulaire
        </h2>
      </div>

      <div className="flex-1 overflow-y-auto no-scrollbar">
        <div className="flex justify-center px-6 pt-6 pb-8">
          <div className="w-full max-w-3xl space-y-4">
            <div className="sticky top-0 z-10 pb-4 bg-base-200">
              <div className="bg-base-100 border border-base-300 rounded-xl p-3 flex gap-2 items-center shadow-sm">
                <input
                  type="text"
                  placeholder="Rechercher par nom de formulaire..."
                  className="input input-bordered input-sm flex-1"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                />
                <button type="button" className="btn btn-primary btn-sm">
                  Rechercher
                </button>
              </div>
            </div>

            <div className="space-y-3">
              {!isLoading
              && !error
              && filtered.map((form) => (
                <ListItemForm key={form.id} {...form} onDelete={handleDelete}/>
              ))}

              {!isLoading && !error && filtered.length === 0 && (
                <p className="text-sm text-base-content/60">
                  Aucun formulaire ne correspond à votre recherche.
                </p>
              )}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
