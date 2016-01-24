jest.dontMock('DeskPRO/Bundle/AgentBundle/Services/Api/Chat.js');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Services/ApiHelpers.js');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Services/DpApi.js');

describe('API Chat service', () => {

  const DpApi = require('DeskPRO/Bundle/AgentBundle/Services/DpApi.js');
  const Chat = require('DeskPRO/Bundle/AgentBundle/Services/Api/Chat.js');

  it('should load chats', () => {
    spyOn(DpApi, 'sendGet');
    Chat.load();
    expect(DpApi.sendGet.argsForCall[0][0]).toEqual('DP_API/user_chats?include=person,agent,department');
  });

  it('should load chats counts', () => {
    spyOn(DpApi, 'sendGet');
    Chat.loadCounts('department', 'me');
    expect(DpApi.sendGet.argsForCall[0][0]).toEqual('DP_API/user_chats/counts?group_by=department&agent=me');
  });
});
