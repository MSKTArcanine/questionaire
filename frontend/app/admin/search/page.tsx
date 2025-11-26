// app/admin/search/page.tsx
'use client';

import { useState } from "react";

const mockForms = [
    { id: 1, title: "Kestuveu ?", description: "On fait genre on met un description" },
    { id: 2, title: "Satisfaction atelier BD", description: "On fait genre on met un description" },
    { id: 3, title: "Parcours jeunes lecteurs", description: "On fait genre on met un description" },
    { id: 4, title: "Parcours jeunes lecteurs", description: "On fait genre on met un description" },
    { id: 5, title: "Parcours jeunes lecteurs", description: "On fait genre on met un description" },
    { id: 6, title: "Parcours jeunes lecteurs", description: "On fait genre on met un description" },
];

export default function AdminSearchFormPage() {
  const [query, setQuery] = useState("");

  const filtered = mockForms.filter((f) =>
    f.title.toLowerCase().includes(query.toLowerCase().trim()),
  );

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
              {filtered.map((form) => (
                <div
                  key={form.id}
                  className="card bg-base-100 border border-base-300 shadow-sm"
                >
                  <div className="card-body flex-row items-center justify-between gap-4">
                    <div className="flex-1">
                      <h3 className="font-semibold text-base">
                        {form.title}
                      </h3>
                      <p className="text-sm text-base-content/60">
                        {form.description}
                      </p>
                    </div>

                    <div className="flex gap-2 shrink-0">
                      <button className="btn btn-sm btn-primary">
                        Ouvrir
                      </button>
                      <button className="btn btn-sm btn-error">
                        Supprimer
                      </button>
                    </div>
                  </div>
                </div>
              ))}

              {filtered.length === 0 && (
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
