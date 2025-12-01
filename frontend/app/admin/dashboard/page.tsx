"use client";

import { DashboardItem, QuestionnaireStats } from "@/lib/types";
import { useEffect, useState } from "react";

export default function AdminDashboardPage() {
    const [items, setItems] = useState<DashboardItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    
    const [selected, setSelected] = useState<QuestionnaireStats | null>(null);
    const [loadingStats, setLoadingStats] = useState(false);
    
    useEffect(() => {
        async function loadDashboard() {
            try {
                setLoading(true);
                setError(null);
                
                const res = await fetch("/api/admin/dashboard", {
                    method: "GET",
                    cache: "no-store",
                });
                
                if (!res.ok) {
                    throw new Error("Erreur lors du chargement du tableau de bord");
                }
                
                const json = await res.json();
                setItems(json.data ?? []);
            } catch (e) {
                console.error(e);
                setError(
                    "Impossible de charger le tableau de bord. Réessayez plus tard."
                );
            } finally {
                setLoading(false);
            }
        }
        
        loadDashboard();
    }, []);
    
    async function handleSelectQuestionnaire(id: number) {
        try {
            setLoadingStats(true);
            setSelected(null);
            
            const res = await fetch(`/api/admin/questionnaires/${id}/stats`, {
                method: "GET",
                cache: "no-store",
            });
            
            if (!res.ok) {
                throw new Error("Erreur lors du chargement des statistiques");
            }
            
            const json: QuestionnaireStats = await res.json();
            setSelected(json);
        } catch (e) {
            console.error(e);
            setError("Impossible de charger les statistiques de ce questionnaire.");
        } finally {
            setLoadingStats(false);
        }
    }
    
    return (
        <main className="min-h-screen bg-base-200 flex flex-col">
        <div className="border-b border-base-300 bg-base-100 px-8 py-4">
        <h1 className="text-2xl font-bold">Tableau de bord des résultats</h1>
        <p className="text-sm text-base-content/70">
        Suivi des questionnaires remplis, export CSV et statistiques.
        </p>
        </div>
        
        <div className="flex-1 flex flex-col md:flex-row gap-4 p-4">
        {/* Liste des questionnaires */}
        <section className="w-full md:w-2/5 flex flex-col gap-3">
        <div className="card bg-base-100 shadow-md border border-base-300 flex-1 overflow-hidden">
        <div className="card-body p-4 gap-3">
        <h2 className="card-title text-base">
        Questionnaires remplis
        </h2>
        
        {loading && (
            <div className="flex items-center gap-2 text-sm">
            <span className="loading loading-spinner loading-sm" />
            <span>Chargement…</span>
            </div>
        )}
        
        {error && (
            <div className="alert alert-error text-sm">
            <span>{error}</span>
            </div>
        )}
        
        {!loading && !error && items.length === 0 && (
            <p className="text-sm text-base-content/60">
            Aucun questionnaire n&apos;a encore été rempli.
            </p>
        )}
        
        {!loading && !error && items.length > 0 && (
            <div className="overflow-y-auto max-h-[60vh] space-y-2">
            {items.map((q) => (
                <div
                key={q.id}
                className="border border-base-300 rounded-xl p-3 bg-base-100 flex flex-col gap-2"
                >
                <div className="flex justify-between items-start gap-2">
                <div>
                <h3 className="font-semibold text-sm">
                {q.title}
                </h3>
                {q.description && (
                    <p className="text-xs text-base-content/60 line-clamp-2">
                    {q.description}
                    </p>
                )}
                </div>
                <span className="badge badge-outline badge-sm">
                {q.finished_sessions} terminés
                </span>
                </div>
                
                <div className="text-xs text-base-content/70 space-y-1">
                <div className="flex items-center justify-between">
                <span>Sessions totales : {q.total_sessions}</span>
                <span>
                Taux complétion : {Math.round(q.completion_rate * 100)}%
                </span>
                </div>
                <progress
                className="progress progress-primary w-full"
                value={Math.round(q.completion_rate * 100)}
                max={100}
                />
                </div>
                
                <div className="flex items-center justify-between gap-2 pt-1">
                <button
                type="button"
                className="btn btn-xs btn-outline"
                onClick={() => handleSelectQuestionnaire(q.id)}
                >
                Voir les stats
                </button>
                
                <a
                href={`/api/admin/questionnaires/${q.id}/export`}
                className="btn btn-xs btn-primary"
                >
                Export CSV
                </a>
                </div>
                </div>
            ))}
            </div>
        )}
        </div>
        </div>
        </section>
        
        {/* GGraphiques */}
        <section className="w-full md:w-3/5 flex flex-col">
        <div className="card bg-base-100 shadow-md border border-base-300 flex-1">
        <div className="card-body p-4 gap-4">
        <h2 className="card-title text-base">
        Statistiques détaillées
        </h2>
        
        {!selected && !loadingStats && (
            <p className="text-sm text-base-content/70">
            Sélectionnez un questionnaire à gauche pour afficher les
            statistiques (répartition des réponses par question
                et par choix).
                </p>
            )}
            
            {loadingStats && (
                <div className="flex items-center gap-2 text-sm">
                <span className="loading loading-spinner loading-sm" />
                <span>Chargement des statistiques…</span>
                </div>
            )}
            
            {selected && !loadingStats && (
                <div className="space-y-6 overflow-y-auto max-h-[70vh]">
                <div>
                <h3 className="font-semibold text-base mb-1">
                {selected.questionnaire.title}
                </h3>
                <p className="text-xs text-base-content/60">
                Répartition des réponses par choix (barres
                    proportionnelles).
                    </p>
                    </div>
                    
                    {selected.questions.map((q) => (
                        <div
                        key={q.id}
                        className="border border-base-300 rounded-xl p-3 space-y-2"
                        >
                        <div className="flex justify-between items-baseline gap-2">
                        <h4 className="font-semibold text-sm">
                        {q.title}
                        </h4>
                        <span className="text-[11px] text-base-content/60">
                        {q.totalAnswers} réponses
                        </span>
                        </div>
                        
                        <div className="space-y-2">
                        {q.choices.map((choice) => {
                            const percent =
                            q.totalAnswers > 0
                            ? Math.round(
                                (choice.count * 100) / q.totalAnswers
                            )
                            : 0;
                            
                            return (
                                <div
                                key={choice.id}
                                className="space-y-1"
                                >
                                <div className="flex justify-between text-[11px]">
                                <span className="truncate pr-2">
                                {choice.content}
                                </span>
                                <span>
                                {choice.count} (
                                    {percent}
                                    %)
                                    </span>
                                    </div>
                                    <progress
                                        className="progress progress-primary w-full"
                                        value={percent}
                                        max={100}
                                        />
                                    </div>
                                );
                            })}
                            </div>
                            </div>
                        ))}
                        </div>
                    )}
                    </div>
                    </div>
                    </section>
                    </div>
                    </main>
                );
            }
            