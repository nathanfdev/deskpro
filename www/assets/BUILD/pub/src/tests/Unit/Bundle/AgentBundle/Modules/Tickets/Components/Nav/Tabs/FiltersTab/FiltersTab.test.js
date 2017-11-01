// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab

jest.dontMock('~components/FiltersTab');

import React from 'react';
import TestUtils from 'react-dom/test-utils';
import { toImmutable } from 'Helpers';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

describe('Tickets Navigation: FiltersTab component', () => {
  const FiltersTab = require('~components/FiltersTab').FiltersTab;
  const NestedList = require('~components/NestedList').NestedList;

  function render() {
    const props = {
      filterSetsCount: toImmutable([{ count: 0, title: 'Test Filter Set' }])
    };

    return renderInTicketsApp({}, <FiltersTab {...props} />);
  }

  it('should render section title', () => {
    const component = render();
    const title     = TestUtils.findRenderedDOMComponentWithClass(component, 'list-sidebar-title');
    expect(title.textContent).toEqual('Test Filter Set');
  });

  it('should render NestedList', () => {
    spyOn(NestedList.prototype, 'render');
    render();
    expect(NestedList.prototype.render).toHaveBeenCalled();
  });
});
