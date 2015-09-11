jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/frame');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/List');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatsList');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ChatsListControlBar');

describe('List', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const List = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/List').List;
  const ListFrame = require('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/frame').ListFrame;
  const ChatsListControlBar =
    require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ChatsListControlBar').ChatsListControlBar;
  const ChatsList = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatsList').ChatsList;
  const ChatsTable = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/ChatsTable').ChatsTable;

  it('should render ListFrame', () => {
    spyOn(ListFrame.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<List elements={[]} viewMode="list" />);
    expect(ListFrame.prototype.render).toHaveBeenCalled();
  });

  it('should render its control bar', () => {
    spyOn(ChatsListControlBar.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<List elements={[]} viewMode="list" />);
    expect(ChatsListControlBar.prototype.render).toHaveBeenCalled();
  });

  it('should render ChatsList when the passed viewMode is "list"', () => {
    spyOn(ChatsList.prototype, 'render').andCallThrough();
    spyOn(ChatsTable.prototype, 'render').andCallThrough();

    TestUtils.renderIntoDocument(<List elements={[]} viewMode="list" />);

    expect(ChatsList.prototype.render).toHaveBeenCalled();
    expect(ChatsTable.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ChatsTable when the passed viewMode is "table"', () => {
    spyOn(ChatsList.prototype, 'render').andCallThrough();
    spyOn(ChatsTable.prototype, 'render').andCallThrough();

    TestUtils.renderIntoDocument(<List elements={[]} viewMode="table" />);

    expect(ChatsTable.prototype.render).toHaveBeenCalled();
    expect(ChatsList.prototype.render).not.toHaveBeenCalled();
  });
});
