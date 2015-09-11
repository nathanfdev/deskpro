// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~ListFrame/frame');
jest.dontMock('~ListFrame/index');
jest.dontMock('~List/List');
jest.dontMock('~List/View/List/ChatsList');
jest.dontMock('~List/ControlBar/ChatsListControlBar');

describe('List', () => {

  const React               = require('react/addons');
  const TestUtils           = React.addons.TestUtils;
  const ListFrame           = require('~ListFrame/frame').ListFrame;
  const List                = require('~List/List').List;
  const ChatsListControlBar = require('~List/ControlBar/ChatsListControlBar').ChatsListControlBar;
  const ChatsList           = require('~List/View/List/ChatsList').ChatsList;
  const ChatsTable          = require('~List/View/Table/ChatsTable').ChatsTable;

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
