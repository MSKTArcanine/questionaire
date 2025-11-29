import { Questionnaire } from "@/lib/types";

export default function QuestionnaireHeader(prop: Readonly<Questionnaire>) {
  return (
    <section className="w-full border-b border-base-300 bg-base-100 px-8 py-4">
      <h2 className="text-2xl font-semibold text-center">
        {prop.title}
      </h2>
      {prop.description && (
        <p className="mt-2 text-center text-base-content/70 max-w-2xl mx-auto">
          {prop.description}
        </p>
      )}
    </section>
  );
}