jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/TableHeader');

import React from 'react';
import TestUtils from 'react-addons-test-utils';

describe('TableHeader', () => {
  const TableHeader = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/Table/TableHeader').TableHeader;

  const Wrapper = React.createClass({
    render: () => {
      return <table><TableHeader /><tbody></tbody></table>;
    },
  });

  it('should render <thead> tag', () => {
    const component = TestUtils.renderIntoDocument(<Wrapper />);
    const thead = TestUtils.findRenderedDOMComponentWithTag(component, 'thead');
    expect(thead).toEqual(jasmine.any(Object));
  });
});
