import { getQuestionnaires } from "../api/questionnaires";

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
    
    it('throw 505 si Internal', async () => {
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
