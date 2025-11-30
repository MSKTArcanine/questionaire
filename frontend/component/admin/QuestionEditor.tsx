'use client';

import { PropQuestionEditor, QuestionType } from "@/lib/types";
import { ChangeEvent, useState } from "react";

export default function QuestionEditor(prop: Readonly<PropQuestionEditor>) {
  const [title, setTitle] = useState(prop.question.title ?? "");
  const [description, setDescription] = useState(prop.question.description ?? "");
  const [answerType, setAnswerType] = useState<QuestionType>(prop.question.type ?? "RADIO");
  const [hasMedia, setHasMedia] = useState<boolean>(!!prop.question.media);
  const [mediaType, setMediaType] = useState<"IMAGE" | "VIDEO">(prop.question.media?.type ?? "IMAGE");
  const [mediaAltText, setMediaAltText] = useState<string>(prop.question.media?.altText ?? "");
  const [mediaFile, setMediaFile] = useState<File | null>(null);

  function handleFileChange(e: ChangeEvent<HTMLInputElement>){
    const file = e.target.files?.[0] ?? null;
    setMediaFile(file);
  }
  
  function handleSave() {
    const trimTitle = title.trim();
    const trimDescription = description.trim();
    const nextQuestion = {
      ...prop.question,
      title: trimTitle,
      description: trimDescription || undefined,
      answerType,
      media: hasMedia ? {
        ...(prop.question.media ?? {}),
        type: mediaType,
        altText: mediaAltText.trim() || undefined,
      }:null,
      mediaFile: hasMedia ? mediaFile ?? null : null
    };
    prop.onSave(nextQuestion);
  }
  
  return (
    <section className="px-8 pt-6 pb-4">
      <div className="max-w-3xl mx-auto bg-base-100 border border-base-300 rounded-xl shadow-md p-6 space-y-5">
        <header className="flex items-center justify-between">
          <h3 className="text-lg font-semibold">
            {prop.question.id === -1 ? "Nouvelle question" : "Question en cours"}
          </h3>
          {prop.question.id !== -1 && (
            <span className="badge badge-outline">ID {prop.question.id}</span>
          )}
        </header>

        <div className="space-y-4">
          {/* Titre */}
          <div className="form-control">
            <p className="label">
              <span className="label-text font-semibold">
                Intitulé de la question
              </span>
            </p>
            <input
              type="text"
              className="input input-bordered"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
            />
          </div>

          {/* Description */}
          <div className="form-control">
            <p className="label">
              <span className="label-text font-semibold">
                Description (facultatif)
              </span>
            </p>
            <input
              type="text"
              className="input input-bordered"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>

          {/* ✅ Checkbox : question avec média ? */}
          <div className="form-control">
            <label className="label cursor-pointer justify-start gap-3">
              <input
                type="checkbox"
                className="checkbox"
                checked={hasMedia}
                onChange={(e) => setHasMedia(e.target.checked)}
              />
              <span className="label-text font-semibold">
                Afficher une image ou une vidéo avant la question
              </span>
            </label>
            <p className="text-xs text-base-content/60 pl-1">
              Si coché, un média sera affiché au-dessus de l’intitulé.
            </p>
          </div>

          {/* ✅ Bloc média conditionnel */}
          {hasMedia && (
            <div className="mt-2 space-y-3 border border-base-300 rounded-lg p-4 bg-base-200/40">
              <p className="text-sm font-semibold">Média de la question</p>

              {/* Type de média */}
              <div className="form-control">
                <p className="label">
                  <span className="label-text">Type de média</span>
                </p>
                <select
                  className="select select-bordered"
                  value={mediaType}
                  onChange={(e) =>
                    setMediaType(e.target.value as "IMAGE" | "VIDEO")
                  }
                >
                  <option value="IMAGE">Image</option>
                  <option value="VIDEO">Vidéo</option>
                </select>
              </div>

              {/* Fichier */}
              <div className="form-control">
                <p className="label">
                  <span className="label-text">Fichier</span>
                </p>
                <input
                  type="file"
                  className="file-input file-input-bordered w-full"
                  accept={
                    mediaType === "IMAGE"
                      ? "image/png,image/jpeg"
                      : "video/mp4"
                  }
                  onChange={handleFileChange}
                />
              </div>

              {/* Alt text */}
              <div className="form-control">
                <p className="label">
                  <span className="label-text">
                    Texte alternatif (accessibilité)
                  </span>
                </p>
                <input
                  type="text"
                  className="input input-bordered"
                  value={mediaAltText}
                  onChange={(e) => setMediaAltText(e.target.value)}
                />
                <p className="text-xs text-base-content/60 pt-1">
                  Décrit brièvement l’image ou la vidéo pour les lecteurs
                  d’écran.
                </p>
              </div>
            </div>
          )}

          {/* Type de réponse (RADIO / MULTIMEDIA) */}
          <div className="form-control">
            <p className="label">
              <span className="label-text font-semibold">Type de réponse</span>
            </p>
            <select
              className="select select-bordered"
              value={answerType}
              onChange={(e) => setAnswerType(e.target.value as QuestionType)}
            >
              <option value="RADIO">Question classique (choix unique)</option>
              <option value="MULTIMEDIA">
                Réponse multimédia (fichier joint)
              </option>
            </select>
          </div>

          {/* Infos sur les choix */}
          <div className="space-y-2">
            <p className="text-sm font-semibold">Choix de cette question</p>
            <p className="text-xs text-base-content/60">
              (La gestion détaillée des choix se fait dans la partie
              “Structure du questionnaire” ci-dessous.)
            </p>
          </div>

          {/* Actions */}
          <div className="flex justify-end gap-2 pt-2">
            <button
              type="button"
              className="btn btn-ghost btn-sm"
              onClick={() => prop.onCancel()}
            >
              Annuler les modifications
            </button>
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={handleSave}
            >
              Enregistrer la question
            </button>
          </div>
        </div>
      </div>
    </section>
  );
  }
  