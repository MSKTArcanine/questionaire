export interface Questionnaire {
    id : number;
    title : string;
    description : string | null;
    rootQuestionId : number | null;
}

export interface Question {
    id : number;
    title : string;
    description : string | null;
    questionnaireId : number;
    type: QuestionType;
}

export interface Choice {
    id: number;
    content : string;
    questionId : number;
    nextQuestionId : number | null;
}

export interface QuestionMedia {
    id: number;
    type: "IMAGE" | "VIDEO";
    mediaName: string;
    mimeType: "png" | "mp4";
    altText?: string;
    question: Question;
}

export interface QuestionWithChoices extends Question {
    choices: Choice[]
}

export interface QuestionnaireWithQuestions extends Questionnaire {
    questions: Question[]
}

export interface QuestionnaireTree extends Questionnaire {
    questions: QuestionWithChoices[]
}

export type QuestionnaireDetail = Questionnaire & { questions: QuestionWithChoices[] }

export interface ApiResponseList<T> {
    data: T[];
}

export interface ApiResponseUnique<T>{
    data: T;
}

export type ApiError = { error : string }
export type ApiDelete = { message : string }

export interface PropListSearch {
    id: number;
    title: string;
    description: string | null;
    onDelete: (id: number) => void;
}

export interface CreateQuestionnairePayload {
    title: string;
    description?: string | null;
    parentChoiceId?: number | null;
    questionnaireId?: number;
    type: QuestionType;
}

export interface PropQuestionEditor {
    question:QuestionNode;
    onSave:CallableFunction;
    onCancel:CallableFunction;
}

export type QuestionType = "RADIO" | "MULTIMEDIA";

export type QuestionNode = {
    id: number;
    title: string;
    description?: string;
    questionnaireId?: number;
    type: QuestionType;
    choices: ChoiceNode[];
    media ?: QuestionMedia |null;
    mediaFile ?: File | null;

};

export type ChoiceNode = {
    id: number;
    content: string;
    next?: QuestionNode;
};

export type UpdateQuestionPayload = {
    title?: string;
    description?: string;
    questionnaireId?:number;
    type?: QuestionType;
};

export type CreateChoicesPayload = {
    questionId: number;
    content: string;
    nextQuestionId?: number | null;
};

export type UpdateChoicePayload = {
    questionId: number;
    content: string;
    nextQuestionId?: number | null;
};

export interface QuestionTreeProps {
    root: QuestionNode;
    onEditQuestion: (question: QuestionNode) => void;
    onDeleteQuestion: (questionId: number) => void;
    onAddQuestionToChoice: (choiceId: number) => void;

    onCreateChoice: (questionId: number, content: string) => void | Promise<void>;
    onUpdateChoice: (choiceId: number, content: string) => void | Promise<void>;
    onDeleteChoice: (choiceId: number) => void | Promise<void>;
}

export type AnswerSession = {
    id: string;
    questionnaire: Questionnaire;
    current_question: QuestionWithChoices | null;
    finished: boolean;
}

export interface AnswerSessionApiResponse {
    data: AnswerSession;
}

export type QuestionNodeProps = {
  question: QuestionWithChoices;
  error: string | null;
  selectedChoiceId: number | null;
  submitting: boolean;
  onSelectChoice: (id: number) => void;
  onNext: () => void;
  canSubmit: boolean;
  file: File | null;
  onFileChange: (file: File | null) => void;
};