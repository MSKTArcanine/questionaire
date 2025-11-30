'use client';

import QuestionEditor from "@/component/admin/QuestionEditor";
import { QuestionTree } from "@/component/admin/QuestionTree";
import { createChoice, deleteChoice, updateChoice } from "@/lib/api/choices";
import { getQuestionnaire } from "@/lib/api/questionnaires";
import { createQuestion, deleteQuestion, getQuestionTree, updateQuestion, uploadQuestionMedia } from "@/lib/api/questions";
import { QuestionNode } from "@/lib/types";
import updateQuestionInTree from "@/lib/utils/updateQuestionInTree";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";

export default function AdminQuestionDetailPage() {

  const params = useParams<{ questionnaireId: string}>();
  const questionnaireIdParam = Number(params.questionnaireId);

  const [activeQuestion, setActiveQuestion] = useState<QuestionNode | null>(
    null,
  );
  const [parentChoiceIdForNewQuestion, setParentChoiceIdForNewQuestion] = useState<number | null>(null);
  const [questionTree, setQuestionTree] = useState<QuestionNode | null>(null);
  const [rootQuestionId, setRootQuestionId] = useState<number | null>(null);
  const [questionnaireTitle, setQuestionnaireTitle] = useState<string>("");
  
  async function reloadTree(currentRootId?: number | null) {
    try {
      const questionnaire = await getQuestionnaire(questionnaireIdParam);
      setQuestionnaireTitle(questionnaire.title);
      setRootQuestionId(questionnaire.rootQuestionId);

      if(!questionnaire.rootQuestionId){
        setQuestionTree(null);
        return;
      }

      const root = await getQuestionTree(currentRootId ?? questionnaire.rootQuestionId);
      setQuestionTree(root);
    }catch (error) {
      console.error("Erreur rechargement arbre :", error);
    }
  }

  useEffect(() => {
    (async () => {
      try{
        await reloadTree();
      } catch (error) {
        console.error("Erreur chargement questionnaire : ", error);
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [questionnaireIdParam]);
  
  async function handleCreateChoice(questionId: number, content: string) {
    try {
      await createChoice({
        questionId,
        content,
        nextQuestionId: null,
      });
      
      await reloadTree();
    } catch (error) {
      console.error("Erreur création du choix :", error);
    }
  }
  
  async function handleSaveQuestion(updatedQuestion: QuestionNode & {
    mediaFile?: File | null
  }) {

    try {

    let savedQuestion: QuestionNode | null = null;

      //Update
      if (updatedQuestion.id !== -1) {
        savedQuestion = await updateQuestion(updatedQuestion.id, {
          title: updatedQuestion.title,
          description: updatedQuestion.description,
          type: updatedQuestion.type,
        });

        // Upload du média si existant
        if(updatedQuestion.mediaFile){
          await uploadQuestionMedia(savedQuestion.id, {
            file: updatedQuestion.mediaFile,
            type: updatedQuestion.media?.type ?? "IMAGE",
            altText: updatedQuestion.media?.altText,
          });
        }
          await reloadTree();
          setActiveQuestion(null);
          setParentChoiceIdForNewQuestion(null);
          return;
      }
      //Creation nouvelle question
      if(questionnaireIdParam == null){
        console.error("Pas de questionnaire");
        setActiveQuestion(null);
        return;
      }
        const createdQuestion = await createQuestion({
          title: updatedQuestion.title,
          description: updatedQuestion.description,
          questionnaireId: questionnaireIdParam,
          type: updatedQuestion.type,
        });

        savedQuestion = createdQuestion;

        if(parentChoiceIdForNewQuestion == null){
          setRootQuestionId(createdQuestion.id);
        }else{
          await updateChoice(parentChoiceIdForNewQuestion, {nextQuestionId:createdQuestion.id});
        }

        //upload media
        if(updatedQuestion.mediaFile){
          await uploadQuestionMedia(createdQuestion.id, {
            file: updatedQuestion.mediaFile,
            type: updatedQuestion.media?.type ?? "IMAGE",
            altText: updatedQuestion.media?.altText,
          });
        }

        await reloadTree();
        setActiveQuestion(null);
        setParentChoiceIdForNewQuestion(null);
        return;
      }catch(e){
        console.error("Erreur sauvegarde : ", e);
      }
  }
  
  async function handleUpdateChoice(choiceId: number, content: string) {
    const trimmed = content.trim();
    if (!trimmed) return;
    
    try {
      await updateChoice(choiceId, { content: trimmed });
      await reloadTree();
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
      type:"RADIO",
      choices: [],
    });
  }
  async function handleDeleteChoice(choiceId: number) {
    try {
      await deleteChoice(choiceId);
      
      await reloadTree();
    } catch (error) {
      console.error("Erreur lors de la suppression du choix :", error);
    }
  }
    async function handleDeleteQuestion(questionId: number) {
    try {
      await deleteQuestion(questionId);
      
      await reloadTree();
    } catch (error) {
      console.error("Erreur lors de la suppression de la question :", error);
    }
  }
  
  return (
    <>
    <div className="border-b border-base-300 bg-base-100 px-8 py-4 shrink-0">
    <h2 className="text-2xl font-bold text-center">
    Édition du questionnaire : {questionnaireTitle}
    </h2>
    </div>
    
    {activeQuestion && (
      <QuestionEditor key={activeQuestion.id} question={activeQuestion} onSave={handleSaveQuestion} onCancel={() => {setActiveQuestion(null); setParentChoiceIdForNewQuestion(null)}}/>
    )}
    
    <section className="flex-1 overflow-y-auto no-scrollbar px-4 sm:px-8 pt-2 pb-8">
    <div className="max-w-5xl mx-auto space-y-4">
    <h3 className="text-lg font-semibold">
    Structure du questionnaire
    </h3>
    <p className="text-sm text-base-content/60">
    Cliquez sur une réponse pour dérouler sa sous-question.  
    Utilisez “Éditer cette question” pour modifier le texte de la
    question, et “Éditer la réponse / Supprimer la réponse” pour gérer
    les libellés de réponses.
    </p>
    
    <div className="mt-3">{questionTree ? (
      <QuestionTree
      root={questionTree}
      onEditQuestion={setActiveQuestion}
      onDeleteQuestion={handleDeleteQuestion}
      onAddQuestionToChoice={handleAddQuestionForChoice}
      onCreateChoice={handleCreateChoice}
      onUpdateChoice={handleUpdateChoice}
      onDeleteChoice={handleDeleteChoice}
      />
    ) : (
      <div className="border border-dashed border-base-300 rounded-xl p-6 text-center text-sm text-base-content/70">
      Ce questionnaire n’a pas encore de question principale.
      <br />
      <button
        type="button"
        className="btn btn-sm btn-primary mt-3"
        onClick={() => {
          setParentChoiceIdForNewQuestion(null);
          setActiveQuestion({
            id: -1,
            title: "",
            description: "",
            type: "RADIO",
            choices: [],
          });
        }}
      >
        Créer la première question
      </button>
    </div>
    )}</div>
    </div>
    </section>
    </>
  );
}
