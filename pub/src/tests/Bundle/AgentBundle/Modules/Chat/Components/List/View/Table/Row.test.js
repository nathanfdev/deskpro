jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row');

describe('Row', () => {
  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const Row = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row').Row;

  const Wrapper = React.createClass({
    render: () => {
      return <table><tbody><Row element={{}} /></tbody></table>;
    },
  });

  it('should render <tr> tag', () => {
    const component = TestUtils.renderIntoDocument(<Wrapper />);
    const tr = TestUtils.findRenderedDOMComponentWithTag(component, 'tr');
    expect(tr).toEqual(jasmine.any(Object));
  });
});
