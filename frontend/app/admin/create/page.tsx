'use client';

import { createQuestionnaire } from "@/lib/api/questionnaires";
import { CreateQuestionnairePayload } from "@/lib/types";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export default function AdminCreateFormPage() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<boolean>(false);
  const router = useRouter();

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setIsSubmitting(true);
    setError(null);
    setSuccess(false);

    const formData = new FormData(event.currentTarget);
    const rawTitle = formData.get('title');
    const title =
      typeof rawTitle === 'string'
        ? rawTitle.trim()
        : '';

    const rawDescription = formData.get('description');
    const descriptionValue =
      typeof rawDescription === 'string'
        ? rawDescription.trim()
        : '';
    //Zzz SONAR. VRAIMENT. gneugneu c'est un String | File.

const description = descriptionValue === '' ? null : descriptionValue;
    const payload:CreateQuestionnairePayload = { title, description };

    try {
      await createQuestionnaire(payload);
      setSuccess(true);
      router.push('/admin/search'); //TODO: Vers la page des détails.
    }catch(err){
      setError(err instanceof Error ? err.message : 'Erreur inconnue');
    }finally{
      setIsSubmitting(false);
    }
  }
  return (<>
          <div className="border-b border-base-300 bg-base-100 px-8 py-4">
            <h2 className="text-2xl font-bold text-center">
              Création du formulaire
            </h2>
          </div>

          <div className="flex justify-center items-start p-8">
            <form
              className="card w-full max-w-xl bg-base-100 shadow-lg"
              onSubmit={(e) => {
                handleSubmit(e);
              }}
            >
              <div className="card-body gap-6">
                <div className="form-control gap-2">
                  {error && (
                    <div className="alert alert-error py-2 text-sm">
                      <span>{error}</span>
                    </div>
                  )}
                  {success && (
                    <div className="alert alert-success py-2 text-sm">
                      <span>Formulaire créé avec succès !</span>
                    </div>
                  )}
                  <p className="label pl-1">
                    <span className="label-text font-semibold">
                      Titre du formulaire
                    </span>
                  </p>
                  <div className="pl-4">
                    <input
                      type="text"
                      placeholder="Titre ici"
                      className="input input-bordered w-full"
                      name="title"
                      required
                    />
                  </div>
                </div>

                <div className="form-control gap-2">
                  <p className="label pl-1">
                    <span className="label-text font-semibold">
                      Description du formulaire
                    </span>
                  </p>
                  <div className="pl-4">
                    <input
                      type="text"
                      placeholder="Description ici"
                      className="input input-bordered w-full"
                      name="description"
                    />
                  </div>
                  <p className="label pl-4">
                    <span className="label-text-alt text-base-content/60">{/* WCAG */}
                      Optionnel, mais recommandé pour les usagers.
                    </span>
                  </p>
                </div>

                <div className="card-actions justify-center pt-2">
                  <button type="submit" className="btn btn-primary px-6" disabled={isSubmitting}>
                    Ajouter +
                  </button>
                </div>
              </div>
            </form>
          </div>
          </>
  );
}
