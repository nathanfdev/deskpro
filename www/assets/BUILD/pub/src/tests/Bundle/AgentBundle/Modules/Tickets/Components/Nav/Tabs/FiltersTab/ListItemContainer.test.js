// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab
// #define ~common DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame

jest.dontMock('~components/ListItemContainer');
jest.mock('~common/Lists/ListItemStatefulContainer');

import React from 'react';
import ReactDOM from 'react-dom';
import TestUtils from 'react-addons-test-utils';
import { toImmutable } from 'Helpers';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

describe('Tickets Navigation: ListItemContainer component', () => {
  const ListItemContainer = require('~components/ListItemContainer').ListItemContainer;
  const ListItemStatefulContainer = require('~common/Lists/ListItemStatefulContainer').ListItemStatefulContainer;

  function render() {
    const props = {
      count: 1, id: 1, title: 'Filter #1', type: 'waiting_time', isTopLevel: true
    };
    renderInTicketsApp({}, <ListItemContainer {...props} />);
  }

  it('should render ListItemStatefulContainer', () => {
    spyOn(ListItemStatefulContainer.prototype, 'render');
    render();
    expect(ListItemStatefulContainer.prototype.render).toHaveBeenCalled()
  });
});
