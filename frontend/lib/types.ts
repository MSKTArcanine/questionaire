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
    //TODO: ZOD surement après en (type) ...
}