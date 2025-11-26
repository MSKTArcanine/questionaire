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

export async function createChoice(): Promise<void>{
    //TODO:A completer.
}