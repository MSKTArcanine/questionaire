import { QuestionNode, QuestionType } from "../types";

export default function mapApiQuestionToNode(data: ApiQuestion): QuestionNode {
  return {
    id: data.id,
    title: data.title,
    description: data.description ?? undefined,
    questionnaireId: data.questionnaireId ?? undefined,
    type: (data.type ?? "RADIO"),
    choices: (data.choices ?? []).map((c: ApiQuestionChoice) => ({
      id: c.id,
      content: c.content,
      next: c.nextQuestion
        ? mapApiQuestionToNode(c.nextQuestion)
        : undefined,
    })),
  }
}

type ApiQuestionChoice = {
  id: number;
  content: string;
  nextQuestion?: ApiQuestion | null;
};

type ApiQuestion = {
  id: number;
  title: string;
  description?: string | null;
  choices?: ApiQuestionChoice[] | null;
  type?: QuestionType | null;
  questionnaireId?: number | null;
};