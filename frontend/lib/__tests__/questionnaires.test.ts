import { getQuestionnaire, getQuestionnaires } from "../api/questionnaires";

type FetchMock = jest.MockedFunction<typeof fetch>;
let fetchMock:FetchMock;

describe('getQuestionnaires', () => {
    beforeEach(() => {
        fetchMock = jest.fn() as FetchMock;
        globalThis.fetch = fetchMock;
    });
    
    it('retourne la liste des Questionnaires', async () => {
        const fakeResponse: ApiResponseList<Questionnaire> = {
            data: [
                {"id":1,"title":"Kestuveu ?","description":"Oui.","rootQuestionId":1}
            ],
        };
        
        fetchMock.mockResolvedValue({
            ok: true,
            status: 200,
            json: async () => fakeResponse,
        } as Response);
        
        const result = await getQuestionnaires();
        
        expect(result).toEqual(fakeResponse.data);
        expect(globalThis.fetch).toHaveBeenCalledWith('/api/questionnaires');
    });
    
    it('throw 500 si Internal', async () => {
        fetchMock.mockResolvedValue({
            ok: false,
            status: 500,
            json: async () => ({ error: 'Internal error' }),
        } as Response);
        
        await expect(getQuestionnaires()).rejects.toThrow(
            'Erreur HTTP 500 /questionnaires',
        );
    });
});
describe('getQuestionnaire(id)', () => {
    beforeEach(() => {
        fetchMock = jest.fn() as FetchMock;
        globalThis.fetch = fetchMock;
    });

    const id = 1;

    it('Return un questionnaire par id', async() => {
        const fakeResponse: ApiResponseUnique<QuestionnaireDetail> = {
            data: {"id":1,"title":"Kestuveu ?","description":"Oui.","rootQuestionId":1,"questions":[
                {"id":1,"title":"Que cherchez vous ?","description":"kestushersh ?","questionnaireId":1,"choices":[
                    {"id":1,"content":"Livres !","nextQuestionId":2,"questionId":1}
                    ,{"id":2,"content":"Vinyles !","nextQuestionId":3,"questionId":1}]
                },{"id":2,"title":"KelLivre ?","description":null,"questionnaireId":1,"choices":[
                    {"id":3,"content":"test1","nextQuestionId":null,"questionId":2}
                    ,{"id":4,"content":"test2","nextQuestionId":null,"questionId":2}]
                },{"id":3,"title":"KelVinyle","description":null,"questionnaireId":1,"choices":[
                    {"id":5,"content":"Vinyle1","nextQuestionId":null,"questionId":3}
                    ,{"id":6,"content":"Vinyle2","nextQuestionId":null,"questionId":3}]
                }
            ]}
        }
        fetchMock.mockResolvedValue({
            ok:true,
            status:200,
            json: async () => fakeResponse,
        } as Response);

        const result = await getQuestionnaire(id);
        expect(result).toEqual(fakeResponse.data);
        expect(fetchMock).toHaveBeenCalledWith(`/api/questionnaires/${id}`);
    })
    it('throw 500 si Internal', async () => {
        fetchMock.mockResolvedValue({
            ok: false,
            status: 500,
            json: async () => ({ error: 'Internal error' }),
        } as Response);
        
        await expect(getQuestionnaire(id)).rejects.toThrow(
            'Erreur HTTP 500 /questionnaires/{id}',
        );
    });
        it('throw 404 si invalid id', async () => {
        fetchMock.mockResolvedValue({
            ok: false,
            status: 404,
            json: async () => ({ error: 'Invalid id' }),
        } as Response);
        
        await expect(getQuestionnaire(id)).rejects.toThrow(
            'Erreur HTTP 404 /questionnaires/{id}',
        );
    });
});