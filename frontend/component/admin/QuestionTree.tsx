import { ChoiceNode, QuestionNode, QuestionTreeProps } from "@/lib/types";
import { JSX, useState } from "react";

export function QuestionTree(prop : Readonly<QuestionTreeProps>): JSX.Element {
    const [addChoiceForQuestionId, setAddChoiceForQuestionId] = useState<number | null>(null);
    const [newChoiceContent, setNewChoiceContent] = useState<string>("");
    
    const [editingChoiceId, setEditingChoiceId] = useState<number | null>(null);
    const [editingChoiceContent, setEditingChoiceContent] = useState<string>("");
    
    function handleStartEditChoice(choiceId: number, currentContent: string) {
        setEditingChoiceId(choiceId);
        setEditingChoiceContent(currentContent);
    }
    
    async function handleConfirmEditChoice(choiceId: number) {
        const content = editingChoiceContent.trim();
        if(!content) return;
        
        try {
            await prop.onUpdateChoice(choiceId, content);
        } finally {
            setEditingChoiceId(null);
            setEditingChoiceContent("");
        }
    }
    
    function handleCancelEditChoice() {
        setEditingChoiceId(null);
        setEditingChoiceContent("");
    }
    
    async function handleConfirmAddChoice(questionId: number) {
        const content = newChoiceContent.trim();
        if(!content) return;
        
        try {
            await prop.onCreateChoice(questionId, content);
        } finally {
            setAddChoiceForQuestionId(null);
            setNewChoiceContent("");
        }
    }
    
    function handleCancelAddChoice() {
        setAddChoiceForQuestionId(null);
        setNewChoiceContent("");
    }
    
    async function handleDeleteChoice(choiceId: number) {
        try {
            await prop.onDeleteChoice(choiceId);
            if(editingChoiceId === choiceId){
                handleCancelEditChoice();
            }
        } catch (error) {
            console.error("Erreur suppression choix :", error);
        }
    }
    
    function renderQuestion(node : QuestionNode, level = 0) : JSX.Element {
        const allChoices: ChoiceNode[] = node.choices ?? [];
        
        return (
            <div
            key={node.id}
            className={`bg-base-100 border border-base-300 rounded-xl shadow-sm p-4 mt-3 ${level > 0 ? "ml-4 border-l-2 border-base-300" : ""}`}
            style={{ marginLeft: level * 16 }}
            >
            {/* Edition titre */}
            <div className="flex items-start justify-between gap-3 mb-3">
            <div>
            <p className="font-semibold text-base">{node.title}</p>
            {node.description && (
                <p className="text-xs text-base-content/60">{node.description}</p>
            )}
            <p className="text-xs text-base-content/60 mt-1">
            {allChoices.length} choix
            </p>
            </div>
            <button
            type="button"
            className="btn btn-xs btn-outline btn-primary"
            onClick={() => prop.onEditQuestion(node)}
            >
            Éditer cette question
            </button>
            </div>
            {/* Liste des quesitons */}
            <div className="space-y-3">
            {allChoices.map((choice) => {
                const isEditing = editingChoiceId === choice.id;
                const content = isEditing ? editingChoiceContent : choice.content;
                
                return (
                    <div
                    key={choice.id}
                    className="collapse collapse-arrow bg-base-100 border border-base-300"
                    >
                    <input type="checkbox" />
                    
                    <div className="collapse-title text-sm">
                    {isEditing ? (
                        <div className="flex items-center justify-between gap-2">
                        <input
                        type="text"
                        className="input input-bordered input-xs flex-1"
                        value={editingChoiceContent}
                        onChange={(e) => setEditingChoiceContent(e.target.value)}
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
                        className="btn btn-xs btn-outline btn-primary"
                        onClick={(event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            prop.onAddQuestionToChoice(choice.id);
                        }}
                        >
                        + Ajouter une question
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
                        </div>
                        </div>
                    )}
                    </div>
                    
                    <div className="collapse-content pt-2">
                    {choice.next ? (
                        renderQuestion(choice.next, level + 1)
                    ) : (
                        <p className="text-xs text-base-content/60 italic">
                        Aucune sous-question pour l’instant.
                        </p>
                    )}
                    </div>
                    </div>
                );
            })}
            </div>
            
            {addChoiceForQuestionId === node.id ? (
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
                    setAddChoiceForQuestionId(node.id);
                    setNewChoiceContent("");
                }}
                >
                + Ajouter un choix
                </button>
            )}
            </div>
        );}
        return <>{renderQuestion(prop.root)}</>;
    }