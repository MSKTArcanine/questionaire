"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { AnswerSessionApiResponse } from "@/lib/types";

type AnswerSession = AnswerSessionApiResponse["data"];

export default function FormFillPage() {
  const params = useParams<{ slug: string }>();
  const slug = params.slug;

  const [answerSession, setAnswerSession] = useState<AnswerSession | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [selectedChoiceId, setSelectedChoiceId] = useState<number | null>(null);
  const [submitting, setSubmitting] = useState<boolean>(false);

  useEffect(() => {
    if (!slug) return;

    let cancelled = false;

    async function createSession() {
      try {
        setLoading(true);
        setError(null);

        const res = await fetch("/api/sessions", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ slug }),
        });

        if (!res.ok) {
          const text = await res.text().catch(() => "");
          console.error(
            "Erreur création answerSession:",
            res.status,
            text || "<no body>"
          );
          throw new Error(`HTTP ${res.status}`);
        }

        const json = (await res.json()) as AnswerSessionApiResponse;
        if (!cancelled) {
          setAnswerSession(json.data);
          setSelectedChoiceId(null);
        }
      } catch (e) {
        console.error(e);
        if (!cancelled) {
          setError("Impossible de créer la answerSession du questionnaire.");
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    createSession();

    return () => {
      cancelled = true;
    };
  }, [slug]);

  const currentQuestion = answerSession?.current_question ?? null;

  useEffect(() => {
    setSelectedChoiceId(null);
  }, [currentQuestion?.id]);
  
  const questionnaire = answerSession?.questionnaire ?? null;
  const finished = answerSession?.finished ?? false;

  const handleNextClick = async () => {
    if(!answerSession || !currentQuestion || selectedChoiceId === null){
      return;
    }

    try {
      setSubmitting(true);
      setError(null);

      const res = await fetch(`/api/sessions/${answerSession.id}/answers`,
        {
          method: "POST",
          headers: {
            "Content-Type":"application/json",
          },
          body: JSON.stringify({choiceId : selectedChoiceId})
        }
      );

      if(!res.ok){
        const text = await res.text().catch(() => "");
        console.error("Erreur d'envoi :", res.status, text || "no body");
      throw new Error(`HTTP ${res.status}`);
      }

      const body = (await res.json()) as AnswerSessionApiResponse;
      setAnswerSession(body.data);
    }catch(e){
      console.error(e);
      setError("Erreur enregistrement réponse");
    }finally{
      setSubmitting(false);
    }
  };
  // Loading...
  if (loading && !answerSession) {
    return (
      <main className="min-h-screen flex flex-col bg-base-200">
        <header className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
          <h1 className="text-3xl font-bold text-center">
            Remplissage du formulaire
          </h1>
        </header>

        <section className="flex-1 flex items-center justify-center">
          <div className="max-w-md w-full bg-base-100 rounded-box shadow-lg border border-base-300 p-8 text-center">
            <span className="loading loading-spinner loading-lg" />
            <p className="mt-4 text-base-content/70">
              Initialisation du questionnaire…
            </p>
          </div>
        </section>
      </main>
    );
  }

  // Erreur
  if (error || !answerSession || !questionnaire) {
    return (
      <main className="min-h-screen flex flex-col bg-base-200">
        <header className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
          <h1 className="text-3xl font-bold text-center">
            Remplissage du formulaire
          </h1>
        </header>

        <section className="flex-1 flex items-center justify-center px-8 py-8">
          <div className="max-w-md w-full bg-base-100 rounded-box shadow-lg border border-error p-8 text-center">
            <h2 className="text-2xl font-semibold mb-4">Oups…</h2>
            <p className="text-base-content/70 mb-4">
              {error ??
                "Une erreur est survenue lors du chargement du questionnaire."}
            </p>
            <p className="text-xs text-base-content/50 break-all">
              slug = {params.slug}
            </p>
          </div>
        </section>
      </main>
    );
  }
  // pas de session ou questionnaire
  if(!answerSession || !questionnaire){
    return null;
  }

  // Session fini
  if (finished || !currentQuestion) {
    return (
      <main className="min-h-screen flex flex-col bg-base-200">
        <header className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
          <h1 className="text-3xl font-bold text-center">
            Remplissage du formulaire
          </h1>
        </header>

        <section className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
          <h2 className="text-2xl font-semibold text-center">
            {questionnaire.title}
          </h2>
          {questionnaire.description && (
            <p className="mt-2 text-center text-base-content/70 max-w-2xl mx-auto">
              {questionnaire.description}
            </p>
          )}
        </section>

        <section className="flex-1 flex items-center justify-center px-8 py-8">
          <div className="max-w-3xl w-full bg-base-100 rounded-box shadow-lg border border-base-300 p-10 text-center">
            <h3 className="text-2xl font-semibold mb-4">
              Merci d&apos;avoir répondu à ce questionnaire
            </h3>
            <p className="text-base-content/70">
              Vous pouvez désormais fermer cette fenêtre ou revenir à l&apos;accueil
              de la borne.
            </p>
          </div>
        </section>

        <footer className="w-full border-t border-base-300 bg-base-100 px-8 py-4">
          <div className="flex gap-4 text-sm text-base-content/70">
            <button className="btn btn-ghost btn-xs rounded-none">
              Mentions légales
            </button>
            <button className="btn btn-ghost btn-xs rounded-none">
              Contact
            </button>
            <button className="btn btn-ghost btn-xs rounded-none">
              Je sais pas...
            </button>
          </div>
        </footer>
      </main>
    );
  }

  // Layout principal avec la question et les radios
  return (
    <main className="min-h-screen flex flex-col bg-base-200">
      {/* header */}
      <header className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
        <h1 className="text-3xl font-bold text-center">
          Remplissage du formulaire
        </h1>
      </header>

      {/* Titre formulaire */}
      <section className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
        <h2 className="text-2xl font-semibold text-center">
          {questionnaire.title}
        </h2>
        {questionnaire.description && (
          <p className="mt-2 text-center text-base-content/70 max-w-2xl mx-auto">
            {questionnaire.description}
          </p>
        )}
      </section>

      {/* Zone centrale question + bouton suivant */}
      <section className="flex-1 flex flex-row gap-4 px-8 py-8">
        {/* Colonne centrale */}
        <div className="flex-1 flex flex-col items-stretch border border-dashed border-error rounded-xl p-8 bg-base-100">
          {/* Titre & description de la question */}
          <div className="mb-8 text-center">
            <h3 className="text-2xl font-semibold mb-2">
              {currentQuestion.title}
            </h3>
            {currentQuestion.description && (
              <p className="text-base-content/70">
                {currentQuestion.description}
              </p>
            )}
          </div>

          {/* Message d'erreur */}
          {error && (
            <p className="mb-4 text-error text-sm text-center">{error}</p>
          )}

          {/* Carte choices */}
          <div className="flex-1 flex items-center justify-center">
            <div className="w-full max-w-3xl border border-base-300 rounded-xl p-6">
              <form className="flex flex-col gap-3">
                {currentQuestion.choices.map((choice) => (
                  <label
                    key={choice.id}
                    className="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200 cursor-pointer hover:bg-base-300 transition-colors"
                  >
                    <input
                      type="radio"
                      name={`question-${currentQuestion.id}`}
                      className="radio radio-sm"
                      checked={selectedChoiceId === choice.id} //Full state pour l'instant
                      onChange={() => setSelectedChoiceId(choice.id)}
                      disabled={submitting}
                    />
                    <span className="text-left leading-snug">
                      {choice.content}
                    </span>
                  </label>
                ))}
              </form>
            </div>
          </div>
        </div>

        {/* Colonne droite pour le bouton Suivant */}
        <aside className="w-40 flex items-center">
          <div className="w-full h-full border border-base-300 rounded-xl flex items-center justify-center">
            <button
              type="button"
              className="btn btn-primary btn-block max-w-[120px]"
              onClick={handleNextClick}
              disabled={selectedChoiceId === null || submitting}
            >
              {submitting ? (
                <>
                  <span className="loading loading-spinner loading-xs mr-2"/>
                  Envoi...
                </>
              ):(
                "Suivant"
              )}
            </button>
          </div>
        </aside>
      </section>

      {/* Footer */}
      <footer className="w-full border-t border-base-300 bg-base-100 px-8 py-4">
        <div className="flex gap-4 text-sm text-base-content/70">
          <button className="btn btn-ghost btn-xs rounded-none">
            Mentions légales
          </button>
          <button className="btn btn-ghost btn-xs rounded-none">
            Contact
          </button>
          <button className="btn btn-ghost btn-xs rounded-none">
            Je sais pas...
          </button>
        </div>
      </footer>
    </main>
  );
}
