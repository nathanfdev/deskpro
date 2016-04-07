// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab

jest.dontMock('~components/FiltersTab');

import React from 'react';
import TestUtils from 'react-addons-test-utils';
import { toImmutable } from 'Helpers';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

describe('Tickets Navigation: FiltersTab component', () => {
  const FiltersTab          = require('~components/FiltersTab').FiltersTab;
  const NestedListContainer = require('~components/NestedListContainer').NestedListContainer;

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

  it('should render NestedListContainer', () => {
    spyOn(NestedListContainer.prototype, 'render');
    render();
    expect(NestedListContainer.prototype.render).toHaveBeenCalled();
  });
});
