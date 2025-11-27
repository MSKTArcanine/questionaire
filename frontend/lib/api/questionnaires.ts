import { ApiResponseList, ApiResponseUnique, CreateQuestionnairePayload, Questionnaire } from "../types";

export async function getQuestionnaires():Promise<Questionnaire[]>{
    const res = await fetch("/api/questionnaires");
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questionnaires`);
    }
    const data: ApiResponseList<Questionnaire> = await res.json();
    return data.data;
}

export async function getQuestionnaire(id: number):Promise<Questionnaire>{
    const res = await fetch(`/api/questionnaires/${id}`);
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /questionnaires/{id}`);
    }
    const data: ApiResponseUnique<Questionnaire> = await res.json();
    return data.data;
}

export async function createQuestionnaire(payload: CreateQuestionnairePayload): Promise<Questionnaire>{
    const res = await fetch("/api/questionnaires", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload)
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} POST /questionnaires`);
    }
    const data: ApiResponseUnique<Questionnaire> = await res.json();
    return data.data;
}