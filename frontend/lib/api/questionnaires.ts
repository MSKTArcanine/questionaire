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
        throw new Error(`Erreur HTTP ${res.status} /questionnaires`);
    }
    const data: ApiResponseUnique<Questionnaire> = await res.json();
    return data.data;
}

export async function createQuestionnaire(): Promise<void>{
    //TODO:A completer.
}