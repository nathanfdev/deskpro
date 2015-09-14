jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/TableHeader');

describe('TableHeader', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const TableHeader = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/TableHeader').TableHeader;

  it('should render <thead> tag', () => {
    const component = TestUtils.renderIntoDocument(<TableHeader />);
    const thead = TestUtils.findRenderedDOMComponentWithTag(component, 'thead');
    expect(thead).toEqual(jasmine.any(Object));
  });

});
