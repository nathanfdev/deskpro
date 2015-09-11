jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/TableView');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/ChatsTable');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/TableHeader');

describe('ChatsTable', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const ChatsTable = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/ChatsTable').ChatsTable;
  const Row = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row').Row;
  const TableHeader = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/TableHeader').TableHeader;

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
