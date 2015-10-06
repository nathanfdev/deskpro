// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar

jest.dontMock('~ListFrame/ViewModeSwitcher');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ControlBar/ViewSwitcherContainer');

import { renderInRedux, toImmutable } from 'Helpers/redux';

describe('ViewSwitcherContainer', () => {
  const ViewSwitcherContainer = require('~ControlBar/ViewSwitcherContainer').ViewSwitcherContainer;
  const ViewModeSwitcher = require('~ListFrame/ViewModeSwitcher').ViewModeSwitcher;
  const state = {
    Chat: {
      list: toImmutable({viewModeOptions: [], tableViewFields: [], listViewFields: []})
    }
  };

  it('should connect to Chat.list state', () => {
    spyOn(state.Chat.list, 'get').andCallThrough();
    renderInRedux(state, ViewSwitcherContainer);
    expect(state.Chat.list.get).toHaveBeenCalled();
  });

  it('should render ViewModeSwitcher component', () => {
    spyOn(ViewModeSwitcher.prototype, 'render').andCallThrough();
    renderInRedux(state, ViewSwitcherContainer);
    expect(ViewModeSwitcher.prototype.render).toHaveBeenCalled();
  });
});
