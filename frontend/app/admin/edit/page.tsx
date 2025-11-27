'use client';

import { JSX, useState } from "react";

type QuestionNode = {
  id: number;
  title: string;
  description?: string;
  choices: ChoiceNode[];
};

type ChoiceNode = {
  id: number;
  label: string;
  next?: QuestionNode;
};

type NewChoice = {
  id: number;
  label: string;
  questionId: number;
};

const mockQuestionTree: QuestionNode = {
  id: 1,
  title: "Que cherchez-vous ?",
  description: "Question d’orientation rapide.",
  choices: [
    {
      id: 1,
      label: "Livres",
      next: {
        id: 2,
        title: "Quel type de livre ?",
        description: "Préciser le type d’ouvrage.",
        choices: [
          { id: 4, label: "BD" },
          { id: 5, label: "Romans" },
        ],
      },
    },
    {
      id: 2,
      label: "Vinyles",
      next: {
        id: 3,
        title: "Quel genre musical ?",
        description: "Pour affiner le style musical.",
        choices: [
          { id: 6, label: "Rock" },
          { id: 7, label: "Classique" },
        ],
      },
    },
  ],
};

export default function AdminQuestionDetailPage() {
  // Panneau du haut (question en cours)
  const [activeQuestion, setActiveQuestion] = useState<QuestionNode | null>(
    null,
  );

  // UI pour les choix
  const [addedChoices, setAddedChoices] = useState<NewChoice[]>([]);
  const [choiceLabels, setChoiceLabels] = useState<Record<number, string>>({});

  const [addingChoiceForQuestionId, setAddingChoiceForQuestionId] = useState<
    number | null
  >(null);
  const [newChoiceLabel, setNewChoiceLabel] = useState("");

  const [editingChoiceId, setEditingChoiceId] = useState<number | null>(null);
  const [editingChoiceLabel, setEditingChoiceLabel] = useState("");
  const [deletedChoiceIds, setDeletedChoiceIds] = useState<number[]>([]);
  const [parentChoiceIdForNewQuestion, setParentChoiceIdForNewQuestion] = useState<number | null>(null);

  function getChoiceLabel(choice: ChoiceNode) {
    return choiceLabels[choice.id] ?? choice.label;
  }

  function isChoiceDeleted(choiceId: number) {
    return deletedChoiceIds.includes(choiceId);
  }

  function handleConfirmAddChoice(questionId: number) {
    const label = newChoiceLabel.trim();
    if (!label) return;

    const id = Date.now(); // suffisant pour le mock

    setAddedChoices((prev) => [...prev, { id, questionId, label }]);
    setChoiceLabels((prev) => ({ ...prev, [id]: label }));
    setNewChoiceLabel("");
    setAddingChoiceForQuestionId(null);
  }

  function handleCancelAddChoice() {
    setNewChoiceLabel("");
    setAddingChoiceForQuestionId(null);
  }

  function handleStartEditChoice(choiceId: number, currentLabel: string) {
    setEditingChoiceId(choiceId);
    setEditingChoiceLabel(currentLabel);
  }

  function handleConfirmEditChoice(choiceId: number) {
    const label = editingChoiceLabel.trim();
    if (!label) return;

    setChoiceLabels((prev) => ({ ...prev, [choiceId]: label }));
    setEditingChoiceId(null);
    setEditingChoiceLabel("");
  }

  function handleCancelEditChoice() {
    setEditingChoiceId(null);
    setEditingChoiceLabel("");
  }
function handleAddQuestionForChoice(choiceId: number) {
  setParentChoiceIdForNewQuestion(choiceId);
  setActiveQuestion({
    id: -1,
    title: "",
    description: "",
    choices: [],
  });
}
  function handleDeleteChoice(choiceId: number) {
    setDeletedChoiceIds((prev) => [...prev, choiceId]);
    if(editingChoiceId === choiceId){
      handleCancelEditChoice();
    }
  }

  function renderQuestion(node: QuestionNode, level = 0): JSX.Element {
    const extraChoicesForThisQuestion: ChoiceNode[] = addedChoices
  .filter((c) => c.questionId === node.id)
  .map((c) => ({
    id: c.id,
    label: c.label,
  }));

    const allChoices: ChoiceNode[] = [...node.choices, ...extraChoicesForThisQuestion];

    return (
      <div
        key={node.id}
        className="bg-base-100 border border-base-300 rounded-xl shadow-sm p-4 mt-3"
        style={{ marginLeft: level * 16 }}
      >
        <div className="flex items-start justify-between gap-3 mb-3">
          <div>
            <p className="font-semibold text-base">{node.title}</p>
            {node.description && (
              <p className="text-xs text-base-content/60">
                {node.description}
              </p>
            )}
            <p className="text-xs text-base-content/60 mt-1">
              {allChoices.length} choix
            </p>
          </div>
          <button
            type="button"
            className="btn btn-xs btn-outline btn-primary"
            onClick={() => setActiveQuestion(node)}
          >
            Éditer cette question
          </button>
        </div>

        <div className="space-y-3">
          {allChoices
          .filter((choice) => !isChoiceDeleted(choice.id))
          .map((choice) => {
            const label = getChoiceLabel(choice);
            const isEditing = editingChoiceId === choice.id;

            if (choice.next) {
              return (
    <div
      key={choice.id}
      className="collapse collapse-arrow bg-base-100 border border-base-300"
    >
      <input type="checkbox" />

      <div className="collapse-title text-sm">
        <span>• {label}</span>
      </div>

      <div className="px-4 pb-2 flex justify-end gap-2">
        {isEditing ? (
          <>
            <input
              type="text"
              className="input input-bordered input-xs flex-1"
              value={editingChoiceLabel}
              onChange={(e) => setEditingChoiceLabel(e.target.value)}
            />
            <button
              type="button"
              className="btn btn-xs btn-primary"
              onClick={() => handleConfirmEditChoice(choice.id)}
            >
              Valider
            </button>
            <button
              type="button"
              className="btn btn-xs btn-ghost"
              onClick={handleCancelEditChoice}
            >
              Annuler
            </button>
          </>
        ) : (
          <>
            <button
              type="button"
              className="btn btn-xs btn-outline"
              onClick={(event) => {
                event.preventDefault();
                event.stopPropagation();
                handleStartEditChoice(choice.id, label);
              }}
            >
              Éditer la réponse
            </button>
            <button
              type="button"
              className="btn btn-xs btn-outline btn-error"
              onClick={(event) => {
                event.preventDefault();
                event.stopPropagation();
                handleDeleteChoice(choice.id);
              }}
            >
              Supprimer la réponse
            </button>
          </>
        )}
      </div>

      <div className="collapse-content pt-2">
        {renderQuestion(choice.next, level + 1)}
      </div>
    </div>
  );
            }

            return (
              <div key={choice.id} className="text-sm">
                {isEditing ? (
                  <div className="flex items-center justify-between gap-2">
                    <input
                      type="text"
                      className="input input-bordered input-xs flex-1"
                      value={editingChoiceLabel}
                      onChange={(e) =>
                        setEditingChoiceLabel(e.target.value)
                      }
                    />
                    <div className="flex gap-2">
                      <button
                        type="button"
                        className="btn btn-xs btn-primary"
                        onClick={() => handleConfirmEditChoice(choice.id)}
                      >
                        Valider
                      </button>
                      <button
                        type="button"
                        className="btn btn-xs btn-ghost"
                        onClick={handleCancelEditChoice}
                      >
                        Annuler
                      </button>
                    </div>
                  </div>
                ) : (
                  <div className="flex items-center justify-between gap-2">
  <span>• {label}</span>
  <div className="flex gap-2">
    <button
      type="button"
      className="btn btn-xs btn-outline"
      onClick={(event) => {handleStartEditChoice(choice.id, label); event.stopPropagation();}}
    >
      Éditer la réponse
    </button>

    <button
      type="button"
      className="btn btn-xs btn-outline btn-primary"
      onClick={() => handleAddQuestionForChoice(choice.id)}
    >
      + Ajouter une question
    </button>

    <button
      type="button"
      className="btn btn-xs btn-outline btn-error"
      onClick={() => handleDeleteChoice(choice.id)}
    >
      Supprimer la réponse
    </button>
  </div>
</div>
                )}
              </div>
            );
          })}
        </div>

        {addingChoiceForQuestionId === node.id ? (
          <div className="mt-3 flex items-center gap-2">
            <input
              type="text"
              className="input input-bordered input-sm flex-1"
              placeholder="Intitulé de la nouvelle réponse…"
              value={newChoiceLabel}
              onChange={(e) => setNewChoiceLabel(e.target.value)}
            />
            <button
              type="button"
              className="btn btn-sm btn-primary"
              onClick={() => handleConfirmAddChoice(node.id)}
            >
              Ajouter
            </button>
            <button
              type="button"
              className="btn btn-sm btn-ghost"
              onClick={handleCancelAddChoice}
            >
              Annuler
            </button>
          </div>
        ) : (
          <button
            type="button"
            className="btn btn-sm btn-ghost mt-3"
            onClick={() => {
              setAddingChoiceForQuestionId(node.id);
              setNewChoiceLabel("");
            }}
          >
            + Ajouter un choix
          </button>
        )}
      </div>
    );
  }

  return (
    <>
      <div className="border-b border-base-300 bg-base-100 px-8 py-4 shrink-0">
        <h2 className="text-2xl font-bold text-center">
          Édition du questionnaire
        </h2>
      </div>

      {activeQuestion && (
        <section className="px-8 pt-6 pb-4">
          <div className="max-w-3xl mx-auto bg-base-100 border border-base-300 rounded-xl shadow-md p-6 space-y-5">
            <header className="flex items-center justify-between">
              <h3 className="text-lg font-semibold">
                {activeQuestion.id === -1
                  ? "Nouvelle question"
                  : "Question en cours"}
              </h3>
              {activeQuestion.id !== -1 && (
                <span className="badge badge-outline">
                  ID {activeQuestion.id}
                </span>
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
                  defaultValue={activeQuestion.title}
                  // TODO: onChange + sauvegarde API
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
                  defaultValue={activeQuestion.description ?? ""}
                />
              </div>

              <div className="space-y-2">
                <p className="text-sm font-semibold">
                  Choix de cette question
                </p>
                <p className="text-xs text-base-content/60">
                  (Pour l’instant, la gestion détaillée des choix se fait dans
                  la partie “Structure du questionnaire” ci-dessous.)
                </p>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  className="btn btn-ghost btn-sm"
                  onClick={() => setActiveQuestion(null)}
                >
                  Annuler les modifications
                </button>
                <button
                    type="button"
                    className="btn btn-primary btn-sm"
                    onClick={() => {
                        // TODO: ici plus tard → appel API + mise à jour de l’arbre
                        setActiveQuestion(null);
                        setParentChoiceIdForNewQuestion(null);
                    }}
                    >
                    Enregistrer la question
                </button>
              </div>
            </div>
          </div>
        </section>
      )}

      <section className="flex-1 overflow-y-auto no-scrollbar px-8 pt-2 pb-8">
        <div className="max-w-3xl mx-auto space-y-4">
          <h3 className="text-lg font-semibold">
            Structure du questionnaire
          </h3>
          <p className="text-sm text-base-content/60">
            Cliquez sur une réponse pour dérouler sa sous-question.  
            Utilisez “Éditer cette question” pour modifier le texte de la
            question, et “Éditer la réponse / Supprimer la réponse” pour gérer
            les libellés de réponses.
          </p>

          <div className="mt-3">{renderQuestion(mockQuestionTree)}</div>
        </div>
      </section>
    </>
  );
}
