import { getChoices } from "../api/choices";
type FetchMock = jest.MockedFunction<typeof fetch>;
let fetchMock:FetchMock;

describe('getChoices', () => {
    beforeEach(() => {
        fetchMock = jest.fn() as FetchMock;
        globalThis.fetch = fetchMock;
    });
    
    it('retourne la liste des choices', async () => {
        const fakeResponse: ApiResponseList<Choice> = {
            data: [
                {"id":1,"content":"Livres !","questionId":1,"nextQuestionId":2},
                {"id":2,"content":"Vinyles !","questionId":1,"nextQuestionId":3},
                {"id":3,"content":"test1","questionId":2,"nextQuestionId":null},
                {"id":4,"content":"test2","questionId":2,"nextQuestionId":null},
                {"id":5,"content":"Vinyle1","questionId":3,"nextQuestionId":null},
                {"id":6,"content":"Vinyle2","questionId":3,"nextQuestionId":null}
            ],
        };
        
        fetchMock.mockResolvedValue({
            ok: true,
            status: 200,
            json: async () => fakeResponse,
        } as Response);
        
        const result = await getChoices();
        
        expect(result).toEqual(fakeResponse.data);
        expect(globalThis.fetch).toHaveBeenCalledWith('/api/choices');
    });
    
    it('throw 505 si Internal', async () => {
        fetchMock.mockResolvedValue({
            ok: false,
            status: 500,
            json: async () => ({ error: 'Internal error' }),
        } as Response);
        
        await expect(getChoices()).rejects.toThrow(
            'Erreur HTTP 500 /choices',
        );
    });
});
