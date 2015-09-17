// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~List/ListContainer');

describe('ListContainer', () => {
  const renderInRedux = require('Helpers/redux').renderInRedux;
  const ListContainer = require('~List/ListContainer').ListContainer;
  const List = require('~List/List').List;
  const state = {Chat: {list: {'get': jasmine.createSpy().andReturn({})}}};

  it('should connect to ChatList state', () => {
    renderInRedux(state, ListContainer);
    expect(state.Chat.list.get).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderInRedux(state, ListContainer);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
