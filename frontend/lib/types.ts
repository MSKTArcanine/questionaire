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
}

export interface Choice {
    id: number;
    content : string;
    questionId : number;
    nextQuestionId : number | null;
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
}

export interface CreateQuestionnairePayload {
    title: string;
    description?: string | null;
    parentChoiceId?: number | null;
    questionnaireId?: number;
    //TODO: ZOD surement après en (type) ...
}

export interface PropQuestionEditor {
    question:QuestionNode;
    onSave:CallableFunction;
    onCancel:CallableFunction;
}

export type QuestionNode = {
    id: number;
    title: string;
    description?: string;
    choices: ChoiceNode[];
};

export type ChoiceNode = {
    id: number;
    content: string;
    next?: QuestionNode;
};

export type UpdateQuestionPayload = {
    title?: string;
    description?: string;
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