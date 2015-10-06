// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar

jest.dontMock('~ListFrame/OrderBy');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ControlBar/OrderByContainer');

import { renderInRedux, toImmutable } from 'Helpers/redux';

describe('OrderByContainer', () => {
  const OrderByContainer = require('~ControlBar/OrderByContainer').OrderByContainer;
  const OrderBy = require('~ListFrame/OrderBy').OrderBy;
  const state = {
    Chat: {
      list: toImmutable({sort: 'id', order: 'desc', sortOptions: [], sortName: 'sort'})
    }
  };

  it('should connect to ChatList state', () => {
    spyOn(state.Chat.list, 'get').andCallThrough();
    renderInRedux(state, OrderByContainer);
    expect(state.Chat.list.get).toHaveBeenCalled();
  });

  it('should render OrderBy component', () => {
    spyOn(OrderBy.prototype, 'render').andCallThrough();
    renderInRedux(state, OrderByContainer);
    expect(OrderBy.prototype.render).toHaveBeenCalled();
  });
});
