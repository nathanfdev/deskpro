// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~ListFrame/TableView');
jest.dontMock('~ListFrame/index');
jest.dontMock('~List/View/Table/ChatsTable');
jest.dontMock('~List/View/Table/Row');
jest.dontMock('~List/View/Table/TableHeader');

describe('ChatsTable', () => {
  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const ChatsTable = require('~List/View/Table/ChatsTable').ChatsTable;
  const Row = require('~List/View/Table/Row').Row;
  const TableHeader = require('~List/View/Table/TableHeader').TableHeader;

  it('should render its header', () => {
    spyOn(TableHeader.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsTable elements={[]} />);
    expect(TableHeader.prototype.render).toHaveBeenCalled();
  });

  it('should render 3 rows when passing 3 children', () => {
    spyOn(Row.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsTable elements={[{}, {}, {}]} />);
    expect(Row.prototype.render.calls.length).toEqual(3);
  });
});
