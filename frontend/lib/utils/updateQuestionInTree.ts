import { QuestionNode } from "../types";

export default function updateQuestionInTree(
  node: QuestionNode,
  updated: QuestionNode,
): QuestionNode {
  if (node.id === updated.id) {
    return {
      ...node,
      title: updated.title,
      description: updated.description,
    };
  }

  const updatedChoices = node.choices.map((choice) => {
    if (choice.next) {
      return {
        ...choice,
        next: updateQuestionInTree(choice.next, updated),
      };
    }
    return choice;
  });

  return {
    ...node,
    choices: updatedChoices,
  };
}