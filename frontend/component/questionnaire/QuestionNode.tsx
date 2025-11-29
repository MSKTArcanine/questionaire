import { QuestionNodeProps } from "@/lib/types";

export default function QuestionNode({
  question,
  error,
  selectedChoiceId,
  submitting,
  onSelectChoice,
  onNext,
}: Readonly<QuestionNodeProps>) {
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

        {/* Message d'erreur */}
        {error && (
          <p className="mb-4 text-error text-sm text-center">{error}</p>
        )}

        {/* Carte choices */}
        <div className="flex-1 flex items-center justify-center">
          <div className="w-full max-w-3xl border border-base-300 rounded-xl p-6">
            <form className="flex flex-col gap-3">
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
            onClick={onNext}
            disabled={selectedChoiceId === null || submitting}
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