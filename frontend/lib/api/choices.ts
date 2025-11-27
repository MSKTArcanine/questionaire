import { ApiResponseList, ApiResponseUnique, Choice, CreateChoicesPayload, UpdateChoicePayload } from "../types";

export async function getChoices():Promise<Choice[]>{
    const res = await fetch("/api/choices");
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /choices`);
    }
    const data: ApiResponseList<Choice> = await res.json();
    return data.data;
}

export async function getChoice(id: number):Promise<Choice>{
    const res = await fetch(`/api/choices/${id}`);
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /choices/{id}`);
    }
    const data: ApiResponseUnique<Choice> = await res.json();
    return data.data;
}

export async function createChoice(payload: CreateChoicesPayload): Promise<Choice>{
    const res = await fetch(`/api/choices`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
        cache: "no-store",
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /choices [POST]`);
    }
    const data: ApiResponseUnique<Choice> = await res.json();
    return data.data;
}

export async function updateChoice(id: number, payload: Partial<UpdateChoicePayload>): Promise<Choice>{
    const res = await fetch(`/api/choices/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
        cache: "no-store",
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /choices/{id} [PUT]`);
    }
    const data: ApiResponseUnique<Choice> = await res.json();
    return data.data;
}

export async function deleteChoice(id: number): Promise<{ message: string }>{
    const res = await fetch(`/api/choices/${id}`, {
        method: "DELETE",
        cache: "no-store",
    });
    if(!res.ok){
        throw new Error(`Erreur HTTP ${res.status} /choices/{id} [DELETE]`);
    }
    const data: { message: string } = await res.json();
    return data;
}