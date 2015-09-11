jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ChatsListControlBar');

describe('ChatsListControlBar', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const ChatsListControlBar =
    require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ChatsListControlBar').ChatsListControlBar;
  const OrderByContainer =
    require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/OrderByContainer').OrderByContainer;
  const ViewSwitcherContainer =
    require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ViewSwitcherContainer').ViewSwitcherContainer;

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
