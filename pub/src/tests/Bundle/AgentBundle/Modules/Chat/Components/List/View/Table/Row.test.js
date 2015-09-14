jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row');

describe('Row', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const Row = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row').Row;

  it('should render <tr> tag', () => {
    const component = TestUtils.renderIntoDocument(<Row element={{}} />);
    const tr = TestUtils.findRenderedDOMComponentWithTag(component, 'tr');
    expect(tr).toEqual(jasmine.any(Object));
  });

});
