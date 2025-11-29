"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { AnswerSessionApiResponse } from "@/lib/types";
import FormFillHeader from "@/component/questionnaire/FormFillHeader";
import FormFillFooter from "@/component/questionnaire/FormFillFooter";
import QuestionnaireHeader from "@/component/questionnaire/QuestionnaireHeader";
import QuestionNode from "@/component/questionnaire/QuestionNode";

type AnswerSession = AnswerSessionApiResponse["data"];

export default function FormFillPage() {
  const params = useParams<{ slug: string }>();
  const slug = params.slug;

  const [answerSession, setAnswerSession] = useState<AnswerSession | null>(
    null
  );
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [selectedChoiceId, setSelectedChoiceId] = useState<number | null>(null);
  const [file, setFile] = useState<File | null>(null);
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
  const isMultimedia = currentQuestion?.type === "MULTIMEDIA";
  const canSubmit = isMultimedia ? !!file : selectedChoiceId !== null;
  // Multimedia = need file, sinon faut un RADIO.

  // Reset du choix quand la question change
  useEffect(() => {
    setSelectedChoiceId(null);
    setFile(null);
  }, [currentQuestion?.id]);

  const questionnaire = answerSession?.questionnaire ?? null;
  const finished = answerSession?.finished ?? false;

  const handleNextClick = async () => {
    if (!answerSession || !currentQuestion) {
      return;
    }

    const isMultimedia = currentQuestion.type === "MULTIMEDIA";

    if(isMultimedia && !file) return;
    if(!isMultimedia && selectedChoiceId === null) return;

    const choiceIdEntreLesDeux = isMultimedia ? currentQuestion.choices[0]?.id ?? null : selectedChoiceId;
    if(!choiceIdEntreLesDeux){ //TODO: Remplacer par leur propre .tsx plus tard.
      return;
    }
    try {
      setSubmitting(true);
      setError(null);

      let res: Response;

      if(isMultimedia){
        const formData = new FormData();
        formData.append("choiceId", String(choiceIdEntreLesDeux));
        formData.append("file", file!);

        res = await fetch(`/api/sessions/${answerSession.id}/answers`, {
          method: "POST",
          body: formData,
        });
      }else{
       res = await fetch(
        `/api/sessions/${answerSession.id}/answers`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ choiceId: choiceIdEntreLesDeux }),
        }
      );}

      if (!res.ok) {
        const text = await res.text().catch(() => "");
        console.error("Erreur d'envoi :", res.status, text || "no body");
        throw new Error(`HTTP ${res.status}`);
      }

      const body = (await res.json()) as AnswerSessionApiResponse;
      setAnswerSession(body.data);
    } catch (e) {
      console.error(e);
      setError("Erreur enregistrement réponse");
    } finally {
      setSubmitting(false);
    }
  };

  // Loading...
  if (loading && !answerSession) {
    return (
      <main className="min-h-screen flex flex-col bg-base-200">
        <FormFillHeader />
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

  // Erreur, pas de session, pas de questionnaire
  if (error || !answerSession || !questionnaire) {
    return (
      <main className="min-h-screen flex flex-col bg-base-200">
        <FormFillHeader />
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
        <FormFillFooter />
      </main>
    );
  }

  // Session finie
  if (finished || !currentQuestion) {
    return (
      <main className="min-h-screen flex flex-col bg-base-200">
        <FormFillHeader />
        <QuestionnaireHeader {...questionnaire} />

        <section className="flex-1 flex items-center justify-center px-8 py-8">
          <div className="max-w-3xl w-full bg-base-100 rounded-box shadow-lg border border-base-300 p-10 text-center">
            <h3 className="text-2xl font-semibold mb-4">
              Merci d&apos;avoir répondu à ce questionnaire
            </h3>
            <p className="text-base-content/70">
              Vous pouvez désormais fermer cette fenêtre ou revenir à
              l&apos;accueil de la borne.
            </p>
          </div>
        </section>

        <FormFillFooter />
      </main>
    );
  }

  // Layout avec la question REFAIRE UN LAYOUT PLUS TARD
  return (
    <main className="min-h-screen flex flex-col bg-base-200">
      <FormFillHeader />
      <QuestionnaireHeader {...questionnaire} />
      <QuestionNode
        question={currentQuestion}
        error={error}
        selectedChoiceId={selectedChoiceId}
        submitting={submitting}
        onSelectChoice={setSelectedChoiceId}
        onNext={handleNextClick}
        canSubmit={canSubmit}
        file={file}
        onFileChange={setFile}
      />
      <FormFillFooter />
    </main>
  );
}
