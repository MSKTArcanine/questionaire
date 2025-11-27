import { QuestionNode } from "../types";

export default function attachNewQuestionToChoice(
  node: QuestionNode,
  parentChoiceId: number,
  newQuestion: QuestionNode,
): QuestionNode {
  const updatedChoices = node.choices.map((choice) => {
    if (choice.id === parentChoiceId && !choice.next) {
      return {
        ...choice,
        next: newQuestion,
      };
    }

    if (choice.next) {
      return {
        ...choice,
        next: attachNewQuestionToChoice(choice.next, parentChoiceId, newQuestion),
      };
    }

    return choice;
  });

  return {
    ...node,
    choices: updatedChoices,
  };
}