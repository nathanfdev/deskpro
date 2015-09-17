// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar
// #define ~Containers DeskPRO/Bundle/AgentBundle/Modules/Chat/Containers/List/ControlBar

jest.dontMock('~ListFrame/ControlBar');
jest.dontMock('~ListFrame/index');
jest.dontMock('~Components/ChatsListControlBar');

describe('ChatsListControlBar', () => {
  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const ChatsListControlBar   = require('~Components/ChatsListControlBar').ChatsListControlBar;
  const OrderByContainer      = require('~Containers/OrderByContainer').OrderByContainer;
  const ViewSwitcherContainer = require('~Containers/ViewSwitcherContainer').ViewSwitcherContainer;

  it('should render OrderByContainer', () => {
    spyOn(OrderByContainer.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsListControlBar />);
    expect(OrderByContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ViewSwitcherContainer', () => {
    spyOn(ViewSwitcherContainer.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsListControlBar />);
    expect(ViewSwitcherContainer.prototype.render).toHaveBeenCalled();
  });

});
