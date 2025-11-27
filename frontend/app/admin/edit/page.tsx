'use client';

import QuestionEditor from "@/component/admin/QuestionEditor";
import { QuestionTree } from "@/component/admin/QuestionTree";
import { createChoice, deleteChoice, updateChoice } from "@/lib/api/choices";
import { createQuestion, getQuestion, getQuestionTree, updateQuestion } from "@/lib/api/questions";
import { QuestionNode } from "@/lib/types";
import updateQuestionInTree from "@/lib/utils/updateQuestionInTree";
import { useEffect, useState } from "react";

export default function AdminQuestionDetailPage() {
  const [activeQuestion, setActiveQuestion] = useState<QuestionNode | null>(
    null,
  );
  const [questionnaireId, setQuestionnaireId] = useState<number | null>(null);
  const [parentChoiceIdForNewQuestion, setParentChoiceIdForNewQuestion] = useState<number | null>(null);
  const [questionTree, setQuestionTree] = useState<QuestionNode | null>(null);
  
  const rootQuestionId = 1; // provisoire, plus tard => param de l’URL
  
  useEffect(() => {
    (async () => {
      try {
        const [rootTree, rootData] = await Promise.all([
          getQuestionTree(rootQuestionId),
          getQuestion(rootQuestionId),
        ]);

        setQuestionTree(rootTree);
        setQuestionnaireId(rootData.questionnaireId);
      } catch (err) {
        console.error("Erreur lors du chargement du questionnaire :", err);
      }
    })();
  }, [rootQuestionId]);
  
  async function handleCreateChoice(questionId: number, content: string) {
    try {
      await createChoice({
        questionId,
        content,
        nextQuestionId: null,
      });
      
      const root = await getQuestionTree(rootQuestionId);
      setQuestionTree(root);
    } catch (error) {
      console.error("Erreur création du choix :", error);
    }
  }
  
  async function handleSaveQuestion(updatedQuestion: QuestionNode) {
    try {
      if (updatedQuestion.id !== -1) {
        const saved = await updateQuestion(updatedQuestion.id, {
          title: updatedQuestion.title,
          description: updatedQuestion.description,
        });
        setQuestionTree((prev) => prev ? updateQuestionInTree(prev, saved) : prev);
        setActiveQuestion(null);
        return;
      }

      if(parentChoiceIdForNewQuestion == null){
        console.error("Pas de parent");
        setActiveQuestion(null);
        return;
      }

      if(questionnaireId == null){
        console.error("Pas de questionnaire");
        setActiveQuestion(null);
        return;
      }

      const createdQuestion = await createQuestion({
        title: updatedQuestion.title,
        description: updatedQuestion.description,
        questionnaireId: questionnaireId,
      });

      await updateChoice(parentChoiceIdForNewQuestion, { //Liaison question -> réponse parent.
        nextQuestionId: createdQuestion.id,
      });

      const root = await getQuestionTree(rootQuestionId);
      setQuestionTree(root);
      setActiveQuestion(null);
      setParentChoiceIdForNewQuestion(null);

    } catch (error) {
      console.error("Erreur sauvegarde question :", error);
    }
  }
  
  async function handleUpdateChoice(choiceId: number, content: string) {
    const trimmed = content.trim();
    if (!trimmed) return;
    
    try {
      await updateChoice(choiceId, { content: trimmed });
      
      const root = await getQuestionTree(rootQuestionId);
      setQuestionTree(root);
    } catch (error) {
      console.error("Erreur lors de la mise à jour du choix :", error);
    }
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
      await deleteChoice(choiceId);
      
      const root = await getQuestionTree(rootQuestionId);
      setQuestionTree(root);
    } catch (error) {
      console.error("Erreur lors de la suppression du choix :", error);
    }
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
    
    <div className="mt-3">{questionTree && (
      <QuestionTree
      root={questionTree}
      onEditQuestion={setActiveQuestion}
      onAddQuestionToChoice={handleAddQuestionForChoice}
      onCreateChoice={handleCreateChoice}
      onUpdateChoice={handleUpdateChoice}
      onDeleteChoice={handleDeleteChoice}
      />
    )}</div>
    </div>
    </section>
    </>
  );
}
