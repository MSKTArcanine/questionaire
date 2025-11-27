'use client';

import { PropQuestionEditor } from "@/lib/types";
import { useState } from "react";

export default function QuestionEditor(prop: Readonly<PropQuestionEditor>) {
  const [title, setTitle] = useState(prop.question.title ?? "");
  const [description, setDescription] = useState(prop.question.description ?? "");

  function handleSave() {
    prop.onSave({
      ...prop.question,
      title: title.trim(),
      description: description.trim() || undefined,
    });
  }

  return (
    <section className="px-8 pt-6 pb-4">
      <div className="max-w-3xl mx-auto bg-base-100 border border-base-300 rounded-xl shadow-md p-6 space-y-5">
        <header className="flex items-center justify-between">
          <h3 className="text-lg font-semibold">
            {prop.question.id === -1 ? "Nouvelle question" : "Question en cours"}
          </h3>
          {prop.question.id !== -1 && (
            <span className="badge badge-outline">ID {prop.question.id}</span>
          )}
        </header>

        <div className="space-y-4">
          <div className="form-control">
            <label className="label">
              <span className="label-text font-semibold">
                Intitulé de la question
              </span>
            </label>
            <input
              type="text"
              className="input input-bordered"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
            />
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text font-semibold">
                Description (facultatif)
              </span>
            </label>
            <input
              type="text"
              className="input input-bordered"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>

          <div className="space-y-2">
            <p className="text-sm font-semibold">Choix de cette question</p>
            <p className="text-xs text-base-content/60">
              (Pour l’instant, la gestion détaillée des choix se fait dans
              la partie “Structure du questionnaire” ci-dessous.)
            </p>
          </div>

          <div className="flex justify-end gap-2 pt-2">
            <button
              type="button"
              className="btn btn-ghost btn-sm"
              onClick={() => prop.onCancel()}
            >
              Annuler les modifications
            </button>
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={handleSave}
            >
              Enregistrer la question
            </button>
          </div>
        </div>
      </div>
    </section>
  );
}
