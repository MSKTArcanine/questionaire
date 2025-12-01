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

export async function getQuestionTree(id: number):Promise<QuestionNode | null>{
    const res = await fetch(`/api/questions/${id}/tree`, {cache: "no-store"});
    if(res.status === 404){ //Suppression de la root.
        return null;
    }
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questions/{id}/tree`);
    }
    const data: ApiResponseUnique<QuestionNode> = await res.json();
    return mapApiQuestionToNode(data.data);
}

export async function deleteQuestion(id: number): Promise<void>{
    const res = await fetch(`/api/questions/${id}`, {
        method: "DELETE",
        cache: "no-store",
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questions/{id} [DELETE]`);
    }
}

export async function uploadQuestionMedia(
    questionId: number,
    options: {
        file: File;
        type: "IMAGE" | "VIDEO";
        altText?: string;
    }
): Promise<QuestionNode>{
    const formData = new FormData();
    formData.append("file", options.file);
    formData.append("mediaType", options.type);
    if(options.altText){
        formData.append("altText", options.altText)
    }

    const res = await fetch(`/api/questions/${questionId}/media`, 
        {
            method: "POST",
            body: formData,
        }
    );

    if(!res.ok){
        const txt = await res.text().catch(() => "");
        throw new Error(`"Erreur upload media HTTP ${res.status} ${txt}`);
    }

    const body = await res.json();
    return mapApiQuestionToNode(body.data);
}