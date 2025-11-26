interface Questionnaire {
    id : number;
    title : string;
    description : string | null;
    rootQuestionId : number | null;
}

interface Question {
    id : number;
    title : string;
    description : string | null;
    questionnaireId : number;
}

interface Choice {
    id: number;
    content : string;
    questionId : number;
    nextQuestionId : number | null;
}

interface QuestionWithChoices extends Question {
    choices: Choice[]
}

interface QuestionnaireWithQuestions extends Questionnaire {
    questions: Question[]
}

interface QuestionnaireTree extends Questionnaire {
    questions: QuestionWithChoices[]
}

type QuestionnaireDetail = Questionnaire & { questions: QuestionWithChoices[] }

interface ApiResponseList<T> {
    data: T[];
}

interface ApiResponseUnique<T>{
    data: T;
}

type ApiError = { error : string }
type ApiDelete = { message : string }