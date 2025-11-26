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
        throw new Error(`Erreur HTTP ${res.status} /questions`);
    }
    const data: ApiResponseUnique<Question> = await res.json();
    return data.data;
}

export async function createQuestion(): Promise<void>{
    //TODO:A completer.
}