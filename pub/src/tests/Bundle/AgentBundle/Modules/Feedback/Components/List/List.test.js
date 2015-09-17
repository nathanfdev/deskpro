// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.dontMock('~ListFrame/frame');
jest.dontMock('~ListFrame/index');
jest.dontMock('~List/List');
jest.dontMock('~List/View/List/FeedbackList');
jest.dontMock('~List/ControlBar/FeedbackListControlBar');

describe('List', () => {

  const React               = require('react/addons');
  const TestUtils           = React.addons.TestUtils;
  const ListFrame           = require('~ListFrame/frame').ListFrame;
  const List                = require('~List/List').List;
  const FeedbackListControlBar = require('~List/ControlBar/FeedbackListControlBar').FeedbackListControlBar;
  const FeedbackList           = require('~List/View/List/FeedbackList').FeedbackList;
  const FeedbackTable          = require('~List/View/Table/FeedbackTable').FeedbackTable;

  it('should render ListFrame', () => {
    spyOn(ListFrame.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<List elements={[]}
                                       viewModeOptions={[{field: 'table', label: 'Table view', icon: 'fa-table', current: true}]}/>);
    expect(ListFrame.prototype.render).toHaveBeenCalled();
  });

  it('should render its control bar', () => {
    spyOn(FeedbackListControlBar.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<List elements={[]}
                                       viewModeOptions={[{field: 'table', label: 'Table view', icon: 'fa-table', current: true}]}/>);
    expect(FeedbackListControlBar.prototype.render).toHaveBeenCalled();
  });

  it('should render FeedbackList when the passed viewMode is "list"', () => {
    spyOn(FeedbackList.prototype, 'render').andCallThrough();
    spyOn(FeedbackTable.prototype, 'render').andCallThrough();

    TestUtils.renderIntoDocument(<List elements={[]}
                                       viewModeOptions={[{field: "list", label: 'List view', icon: 'fa-list', current: true}]}/>);

    expect(FeedbackList.prototype.render).toHaveBeenCalled();
    expect(FeedbackTable.prototype.render).not.toHaveBeenCalled();
  });

  it('should render FeedbackTable when the passed viewMode is "table"', () => {
    spyOn(FeedbackList.prototype, 'render').andCallThrough();
    spyOn(FeedbackTable.prototype, 'render').andCallThrough();

    TestUtils.renderIntoDocument(<List elements={[]} viewModeOptions={[{field: "table", label: 'Table view', icon: 'fa-table', current: true}]}/>);

    expect(FeedbackList.prototype.render).toHaveBeenCalled();
    expect(FeedbackList.prototype.render).not.toHaveBeenCalled();
  });
});
