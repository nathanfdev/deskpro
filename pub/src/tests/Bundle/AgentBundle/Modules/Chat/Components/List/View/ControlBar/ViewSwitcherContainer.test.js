// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar

jest.dontMock('~ListFrame/ListTableViewSwitcher');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ControlBar/ViewSwitcherContainer');

describe('ViewSwitcherContainer', () => {
  const renderInRedux = require('Helpers/redux').renderInRedux;
  const ViewSwitcherContainer = require('~ControlBar/ViewSwitcherContainer').ViewSwitcherContainer;
  const ListTableViewSwitcher = require('~ListFrame/ListTableViewSwitcher').ListTableViewSwitcher;
  const state = {Chat: {list: {'get': jasmine.createSpy().andCallFake((arg) => {
    return {viewMode: 'list', displayFields: []}[arg];
  })}}};

  it('should connect to ChatList state', () => {
    renderInRedux(state, ViewSwitcherContainer);
    expect(state.Chat.list.get).toHaveBeenCalled();
  });

  it('should render ListTableViewSwitcher component', () => {
    spyOn(ListTableViewSwitcher.prototype, 'render').andCallThrough();
    renderInRedux(state, ViewSwitcherContainer);
    expect(ListTableViewSwitcher.prototype.render).toHaveBeenCalled();
  });
});
