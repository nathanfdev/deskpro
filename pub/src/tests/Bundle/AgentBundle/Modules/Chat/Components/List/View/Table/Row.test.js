jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/Row');

import React from 'react';
import TestUtils from 'react-addons-test-utils';

describe('Row', () => {
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
