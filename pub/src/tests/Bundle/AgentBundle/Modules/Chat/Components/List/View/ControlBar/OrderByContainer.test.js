// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar

jest.dontMock('~ListFrame/OrderBy');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ControlBar/OrderByContainer');

describe('OrderByContainer', () => {
  const renderInRedux = require('Helpers/redux').renderInRedux;
  const OrderByContainer = require('~ControlBar/OrderByContainer').OrderByContainer;
  const OrderBy = require('~ListFrame/OrderBy').OrderBy;
  const state = {Chat: {list: {'get': jasmine.createSpy().andCallFake((arg) => {
    return {sort: 'id', order: 'desc', sortOptions: [], sortName: 'sort'}[arg];
  })}}};

  it('should connect to ChatList state', () => {
    renderInRedux(state, OrderByContainer);
    expect(state.Chat.list.get).toHaveBeenCalled();
  });

  it('should render OrderBy component', () => {
    spyOn(OrderBy.prototype, 'render').andCallThrough();
    renderInRedux(state, OrderByContainer);
    expect(OrderBy.prototype.render).toHaveBeenCalled();
  });
});
