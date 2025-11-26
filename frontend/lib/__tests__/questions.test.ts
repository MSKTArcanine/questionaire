import { getQuestions } from "../api/questions";

type FetchMock = jest.MockedFunction<typeof fetch>;
let fetchMock:FetchMock;

describe('getQuestions', () => {
    beforeEach(() => {
        fetchMock = jest.fn() as FetchMock;
        globalThis.fetch = fetchMock;
    });
    
    it('retourne la liste des Questions', async () => {
        const fakeResponse: ApiResponseList<Question> = {
            data: [
                {"id":1,"title":"Que cherchez vous ?","description":"kestushersh ?","questionnaireId":1},
                {"id":2,"title":"KelLivre ?","description":null,"questionnaireId":1},
                {"id":3,"title":"KelVinyle","description":null,"questionnaireId":1}
            ],
        };
        
        fetchMock.mockResolvedValue({
            ok: true,
            status: 200,
            json: async () => fakeResponse,
        } as Response);
        
        const result = await getQuestions();
        
        expect(result).toEqual(fakeResponse.data);
        expect(globalThis.fetch).toHaveBeenCalledWith('/api/questions');
    });
    
    it('throw 500 si Internal', async () => {
        fetchMock.mockResolvedValue({
            ok: false,
            status: 500,
            json: async () => ({ error: 'Internal error' }),
        } as Response);
        
        await expect(getQuestions()).rejects.toThrow(
            'Erreur HTTP 500 /questions',
        );
    });
});
