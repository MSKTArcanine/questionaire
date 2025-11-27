import { QuestionNode } from "../types";

export default function mapApiQuestionToNode(data: any): QuestionNode {
  return {
    id: data.id,
    title: data.title,
    description: data.description ?? undefined,
    choices: (data.choices ?? []).map((c: any) => ({
      id: c.id,
      content: c.content,
      next: c.nextQuestion
        ? mapApiQuestionToNode(c.nextQuestion)
        : undefined,
    })),
  }
}