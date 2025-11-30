import { QuestionNodeProps } from "@/lib/types";
import Image from "next/image";

export default function QuestionNode({
  question,
  error,
  selectedChoiceId,
  submitting,
  onSelectChoice,
  onNext,
  canSubmit,
  file,
  onFileChange,
}: Readonly<QuestionNodeProps>) {

  const isMultimedia = question.type === "MULTIMEDIA";
  const media = question.media ?? null;

  return (
    <section className="flex-1 flex flex-row gap-4 px-8 py-8">
      {/* Colonne centrale */}
      <div className="flex-1 flex flex-col items-stretch border border-dashed border-error rounded-xl p-8 bg-base-100">
        {/* Titre & description de la question */}
        <div className="mb-8 text-center">
          <h3 className="text-2xl font-semibold mb-2">{question.title}</h3>
          {question.description && (
            <p className="text-base-content/70">{question.description}</p>
          )}
        </div>

        {media && (
          <div className="mb-6 flex justify-center">
            {media.type === "IMAGE" ? (
              <Image
                src={media.streamUrl}
                alt={media.altText ?? ""}
                width={300}
                height={300}
                unoptimized
                className="max-h-64 rounded-xl shadow-md object-contain"
                />
            ):(
              <video
                src={media.streamUrl}
                controls
                className="max-h-64 rounded-xl shadow-md"
                />
            )}
            </div>
        )}

        {/* Message d'erreur */}
        {error && (
          <p className="mb-4 text-error text-sm text-center">{error}</p>
        )}

        {/* Carte choices */}
        <div className="flex-1 flex items-center justify-center">
          <div className="w-full max-w-3xl border border-base-300 rounded-xl p-6">{
            isMultimedia ? (
    <div className="flex flex-col gap-4">
      <p className="text-sm text-base-content/70">
        Importez votre image ou vidéo (formats supportés : PNG, MP4…).
      </p>
      <input
        type="file"
        className="file-input file-input-bordered w-full"
        accept="image/*,video/*"
        onChange={(e) =>
          onFileChange(e.target.files?.[0] ?? null)
        }
        disabled={submitting}
      />
      {file && (
        <p className="text-xs text-base-content/60">
          Fichier sélectionné : <span className="font-semibold">{file.name}</span>
        </p>
      )}
    </div>
            ):(<form className="flex flex-col gap-3">
              {question.choices.map((choice) => (
                <label
                  key={choice.id}
                  className="flex items-center gap-3 px-4 py-3 rounded-xl bg-base-200 cursor-pointer hover:bg-base-300 transition-colors"
                >
                  <input
                    type="radio"
                    name={`question-${question.id}`}
                    className="radio radio-sm"
                    checked={selectedChoiceId === choice.id}
                    onChange={() => onSelectChoice(choice.id)}
                    disabled={submitting}
                  />
                  <span className="text-left leading-snug">
                    {choice.content}
                  </span>
                </label>
              ))}
            </form>)}
          </div>
        </div>
      </div>

      {/* Colonne droite pour le bouton Suivant */}
      <aside className="w-40 flex items-center">
        <div className="w-full h-full border border-base-300 rounded-xl flex items-center justify-center">
          <button
            type="button"
            className="btn btn-primary btn-block max-w-[120px]"
            onClick={onNext}
            disabled={!canSubmit || submitting}
          >
            {submitting ? (
              <>
                <span className="loading loading-spinner loading-xs mr-2" />
                {" "}
                Envoi...
              </>
            ) : (
              "Suivant"
            )}
          </button>
        </div>
      </aside>
    </section>
  );
}