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
    
    async function handleDeleteQuestion(questionId: number) {
        if (!confirm("Supprimer cette question et toutes ses réponses ?")) {
            return;
        }
        
        try {
            await prop.onDeleteQuestion(questionId);
        } catch (error) {
            console.error("Erreur suppression question :", error);
        }
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
    
    function renderQuestion(node: QuestionNode, level = 0): JSX.Element {
        const allChoices: ChoiceNode[] = node.choices ?? [];
        
        return (
            <div
            key={node.id}
            className={`bg-base-100 border border-base-300 rounded-xl shadow-sm p-4 mt-3 ${
                level > 0 ? "ml-4 border-l-2 border-base-300" : ""
                }`}
                style={{ marginLeft: level * 16 }}
                >
                {/* Titre de question */}
                <div className="flex items-start justify-between gap-3 mb-3">
                <div>
                <p className="font-semibold text-base">
                    {node.title}
                    {node.type === "MULTIMEDIA" && (<span className="badge badge-info badge-xs ml-2"> : Multimédia</span>)}    
                </p>
                {node.description && (
                    <p className="text-xs text-base-content/60">{node.description}</p>
                )}
                <p className="text-xs text-base-content/60 mt-1">
                {allChoices.length} choix
                </p>
                </div>
                
                <div className="flex items-end gap-1">
                <button
                type="button"
                className="btn btn-xs btn-outline btn-primary"
                onClick={() => prop.onEditQuestion(node)}
                >
                Éditer cette question
                </button>
                
                <button
                type="button"
                className="btn btn-xs btn-outline btn-error"
                onClick={() => handleDeleteQuestion(node.id)}
                >
                Supprimer cette question
                </button>
                </div>
                </div>
                
                {/* Choix */}
                <div className="space-y-3">
                {allChoices.map((choice) => {
                    const isEditing = editingChoiceId === choice.id;
                    const content = isEditing ? editingChoiceContent : choice.content;
                    
                    // --- CAS AVEC SOUS-QUESTION => collapse ---
                    if (choice.next) {
                        return (
                            <div
                            key={choice.id}
                            className="collapse collapse-arrow bg-base-100 border border-base-300"
                            >
                            <input type="checkbox" />
                            
                            {/* Le titre ne contient QUE le texte cliquable */}
                            <div className="collapse-title text-sm">
                            <span>• {choice.content}</span>
                            </div>
                            
                            {/* Les boutons sont en dehors du titre */}
                            <div className="px-4 pb-2 flex justify-end gap-2">
                            {isEditing ? (
                                <>
                                <input
                                type="text"
                                className="input input-bordered input-xs flex-1"
                                value={editingChoiceContent}
                                onChange={(e) =>
                                    setEditingChoiceContent(e.target.value)
                                }
                                />
                                <button
                                type="button"
                                className="btn btn-xs btn-primary"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleConfirmEditChoice(choice.id);
                                }}
                                >
                                Valider
                                </button>
                                <button
                                type="button"
                                className="btn btn-xs btn-ghost"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleCancelEditChoice();
                                }}
                                >
                                Annuler
                                </button>
                                </>
                            ) : (
                                <>
                                <button
                                type="button"
                                className="btn btn-xs btn-outline"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleStartEditChoice(choice.id, content);
                                }}
                                >
                                Éditer la réponse
                                </button>
                                <button
                                type="button"
                                className="btn btn-xs btn-outline btn-primary"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    prop.onAddQuestionToChoice(choice.id);
                                }}
                                >
                                + Ajouter une question
                                </button>
                                <button
                                type="button"
                                className="btn btn-xs btn-outline btn-error"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleDeleteChoice(choice.id);
                                }}
                                >
                                Supprimer la réponse
                                </button>
                                </>
                            )}
                            </div>
                            
                            {/* tiroir : sousquestion */}
                            <div className="collapse-content pt-2">
                            {renderQuestion(choice.next, level + 1)}
                            </div>
                            </div>
                        );
                    }
                    
                    // --- CAS SANS SOUS-QUESTION => simple ligne, comme avant ---
                    if (!choice.next) {
                        return (
                            <div
                            key={choice.id}
                            className="collapse collapse-arrow bg-base-100 border border-base-300"
                            >
                            <input type="checkbox" />
                            
                            {/* Titre */}
                            <div className="collapse-title text-sm">
                            <span>• {content}</span>
                            </div>
                            
                            {/* Boutons EN DEHORS du titre */}
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
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleConfirmEditChoice(choice.id);
                                }}
                                >
                                Valider
                                </button>
                                <button
                                type="button"
                                className="btn btn-xs btn-ghost"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleCancelEditChoice();
                                }}
                                >
                                Annuler
                                </button>
                                </>
                            ) : (
                                <>
                                <button
                                type="button"
                                className="btn btn-xs btn-outline"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleStartEditChoice(choice.id, content);
                                }}
                                >
                                Éditer la réponse
                                </button>
                                
                                <button
                                type="button"
                                className="btn btn-xs btn-outline btn-primary"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    prop.onAddQuestionToChoice(choice.id);
                                }}
                                >
                                + Ajouter une question
                                </button>
                                
                                <button
                                type="button"
                                className="btn btn-xs btn-outline btn-error"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleDeleteChoice(choice.id);
                                }}
                                >
                                Supprimer la réponse
                                </button>
                                </>
                            )}
                            </div>
                            
                            {/* Contenu du tiroir LEAF */}
                            <div className="collapse-content pt-2">
                            <p className="text-xs text-base-content/60 italic">
                            Aucune sous-question pour l’instant.
                            </p>
                            </div>
                            </div>
                        );
                    }
                })}
                </div>
                
                {/* + Ajouter choix */}
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
            );
        }
        return <>{renderQuestion(prop.root)}</>;
    }