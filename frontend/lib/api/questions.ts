import { ApiResponseList, ApiResponseUnique, CreateQuestionnairePayload, Question, QuestionNode, UpdateQuestionPayload } from "../types";
import mapApiQuestionToNode from "../utils/mapApiQuestionToNode";

export async function getQuestions():Promise<Question[]>{
    const res = await fetch("/api/questions");
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questions`);
    }
    const data: ApiResponseList<Question> = await res.json();
    return data.data;
}

export async function getQuestion(id: number):Promise<Question>{
    const res = await fetch(`/api/questions/${id}`);
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questions/{id}`);
    }
    const data: ApiResponseUnique<Question> = await res.json();
    return data.data;
}

export async function createQuestion(payload: CreateQuestionnairePayload): Promise<QuestionNode>{
    const res = await fetch("/api/questions", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload),
        cache: "no-store"
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} POST /questions`);
    }
    const data: ApiResponseUnique<QuestionNode> = await res.json();
    return mapApiQuestionToNode(data.data);
}

export async function updateQuestion(id: number, payload: UpdateQuestionPayload): Promise<QuestionNode>{
    const res = await fetch(`/api/questions/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload),
        cache: "no-store"
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} PUT /questions/{id}`);
    }
    const data: ApiResponseUnique<QuestionNode> = await res.json();
    return mapApiQuestionToNode(data.data);
}

export async function getQuestionTree(id: number):Promise<QuestionNode>{
    const res = await fetch(`/api/questions/${id}/tree`, {cache: "no-store"});
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questions/{id}/tree`);
    }
    const data: ApiResponseUnique<QuestionNode> = await res.json();
    return mapApiQuestionToNode(data.data);
}