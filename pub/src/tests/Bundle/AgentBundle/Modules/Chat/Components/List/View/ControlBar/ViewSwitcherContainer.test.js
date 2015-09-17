// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar

jest.dontMock('~ListFrame/ListTableViewSwitcher');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ControlBar/ViewSwitcherContainer');

import { renderInRedux, toImmutable } from 'Helpers/redux';

describe('ViewSwitcherContainer', () => {
  const ViewSwitcherContainer = require('~ControlBar/ViewSwitcherContainer').ViewSwitcherContainer;
  const ListTableViewSwitcher = require('~ListFrame/ListTableViewSwitcher').ListTableViewSwitcher;
  const state = {
    Chat: {
      list: toImmutable({viewMode: 'list', tableViewFields: [], listViewFields: []})
    }
  };

  it('should connect to ChatList state', () => {
    spyOn(state.Chat.list, 'get').andCallThrough();
    renderInRedux(state, ViewSwitcherContainer);
    expect(state.Chat.list.get).toHaveBeenCalled();
  });

  it('should render ListTableViewSwitcher component', () => {
    spyOn(ListTableViewSwitcher.prototype, 'render').andCallThrough();
    renderInRedux(state, ViewSwitcherContainer);
    expect(ListTableViewSwitcher.prototype.render).toHaveBeenCalled();
  });
});
