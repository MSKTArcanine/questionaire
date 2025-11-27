'use client';

import QuestionEditor from "@/component/admin/QuestionEditor";
import { deleteChoice, updateChoice } from "@/lib/api/choices";
import { getQuestionTree, updateQuestion } from "@/lib/api/questions";
import { ChoiceNode, QuestionNode } from "@/lib/types";
import updateQuestionInTree from "@/lib/utils/updateQuestionInTree";
import { JSX, useEffect, useState } from "react";

type NewChoice = {
  id: number;
  content: string;
  questionId: number;
};

export default function AdminQuestionDetailPage() {
  const [activeQuestion, setActiveQuestion] = useState<QuestionNode | null>(
    null,
  );

  const [addedChoices, setAddedChoices] = useState<NewChoice[]>([]);
  const [choiceContents, setChoiceContents] = useState<Record<number, string>>({});

  const [addingChoiceForQuestionId, setAddingChoiceForQuestionId] = useState<
    number | null
  >(null);
  const [newChoiceContent, setNewChoiceContent] = useState("");

  const [editingChoiceId, setEditingChoiceId] = useState<number | null>(null);
  const [editingChoiceContent, setEditingChoiceContent] = useState("");
  const [deletedChoiceIds, setDeletedChoiceIds] = useState<number[]>([]);
  const [parentChoiceIdForNewQuestion, setParentChoiceIdForNewQuestion] = useState<number | null>(null);
  const [questionTree, setQuestionTree] = useState<QuestionNode | null>(null);

const rootQuestionId = 1; // provisoire, plus tard => param de l’URL

useEffect(() => {
  (async () => {
    try {
      const root = await getQuestionTree(rootQuestionId);
      setQuestionTree(root);
    } catch (err) {
      console.error("Erreur lors du chargement du questionnaire :", err);
    }
  })();
}, [rootQuestionId]);

  async function handleSaveQuestion(updatedQuestion: QuestionNode) {
  try {
    if (updatedQuestion.id !== -1) {
      const saved = await updateQuestion(updatedQuestion.id, {
        title: updatedQuestion.title,
        description: updatedQuestion.description,
      });
      setQuestionTree((prev) => updateQuestionInTree(prev!, saved));
      setActiveQuestion(null);
      return;
    }

    console.log("TODO: créer une nouvelle question");
  } catch (error) {
    console.error("Erreur lors de la sauvegarde de la question :", error);
  }
}

  function getChoiceContent(choice: ChoiceNode) {
    return choiceContents[choice.id] ?? choice.content;
  }

  function isChoiceDeleted(choiceId: number) {
    return deletedChoiceIds.includes(choiceId);
  }

  function handleConfirmAddChoice(questionId: number) {
    const content = newChoiceContent.trim();
    if (!content) return;

    const id = Date.now(); // suffisant pour le mock

    setAddedChoices((prev) => [...prev, { id, questionId, content }]);
    setChoiceContents((prev) => ({ ...prev, [id]: content }));
    setNewChoiceContent("");
    setAddingChoiceForQuestionId(null);
  }

  function handleCancelAddChoice() {
    setNewChoiceContent("");
    setAddingChoiceForQuestionId(null);
  }

  function handleStartEditChoice(choiceId: number, currentContent: string) {
    setEditingChoiceId(choiceId);
    setEditingChoiceContent(currentContent);
  }

  async function handleConfirmEditChoice(choiceId: number) { //FAIT.
    const content = editingChoiceContent.trim();
    if (!content) return;
    try {
      const isNewChoice = addedChoices.some((c) => c.id === choiceId);
      if(!isNewChoice){
        await updateChoice(choiceId, {content});
      }
      setChoiceContents((prev) => ({ ...prev, [choiceId]: content }));
    } catch (error) { console.error("Erreur edit : ", error);}
    finally {
      setEditingChoiceId(null);
      setEditingChoiceContent("");
    }
  }

  function handleCancelEditChoice() {
    setEditingChoiceId(null);
    setEditingChoiceContent("");
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
  async function handleDeleteChoice(choiceId: number) {
    try {
      const isNewChoice = addedChoices.some((c) => c.id === choiceId);
      if (!isNewChoice) {
        await deleteChoice(choiceId);
      } else {
        setAddedChoices((prev) => prev.filter((c) => c.id !== choiceId));
      }
      
      setDeletedChoiceIds((prev) => [...prev, choiceId]);
      if(editingChoiceId === choiceId){
        handleCancelEditChoice();
      }
    }catch(error){
      console.error("Erreur supp :", error);
    }
  }

  function renderQuestion(node: QuestionNode, level = 0): JSX.Element {
    const extraChoicesForThisQuestion: ChoiceNode[] = addedChoices
  .filter((c) => c.questionId === node.id)
  .map((c) => ({
    id: c.id,
    content: c.content,
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
            const content = getChoiceContent(choice);
            const isEditing = editingChoiceId === choice.id;

            if (choice.next) {
              return (
    <div
      key={choice.id}
      className="collapse collapse-arrow bg-base-100 border border-base-300"
    >
      <input type="checkbox" />

      <div className="collapse-title text-sm">
        <span>• {content}</span>
      </div>

      <div className="px-4 pb-2 flex justify-end gap-2">
        {isEditing ? (
          <>
            <input
              type="text"
              className="input input-bordered input-xs flex-1"
              value={editingChoiceContent}
              onChange={(e) => setEditingChoiceContent(e.target.value)}
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
                handleStartEditChoice(choice.id, content);
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
                      value={editingChoiceContent}
                      onChange={(e) =>
                        setEditingChoiceContent(e.target.value)
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
  <span>• {content}</span>
  <div className="flex gap-2">
    <button
      type="button"
      className="btn btn-xs btn-outline"
      onClick={(event) => {handleStartEditChoice(choice.id, content); event.stopPropagation();}}
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
              value={newChoiceContent}
              onChange={(e) => setNewChoiceContent(e.target.value)}
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
              setNewChoiceContent("");
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
        <QuestionEditor key={activeQuestion.id} question={activeQuestion} onSave={handleSaveQuestion} onCancel={() => {setActiveQuestion(null); setParentChoiceIdForNewQuestion(null)}}/>
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

          <div className="mt-3">{questionTree && renderQuestion(questionTree)}</div>
        </div>
      </section>
    </>
  );
}
