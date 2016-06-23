// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab

jest.dontMock('~components/NestedList');
jest.dontMock('~components/ListItemContainer');

import React from 'react';
import ReactDOM from 'react-dom';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

describe('Tickets Navigation: NestedList component', () => {
  const NestedList  = require('~components/NestedList').NestedList;
  const UrgencyList = require('~components/UrgencyList').UrgencyList;

  const dummyProps = {
    alwaysExpanded: true,
    items:          [
      { count: 1, id: 1, title: 'Filter #1', nested: [] },
      { count: 2, id: 2, title: 'Filter #2', nested: [] },
      {
        count:      3,
        id:         3,
        title:      'Filter #3',
        grouped_by: 'open_time',
        nested:     [
          { count: 1, id: '1m', title: '1m', type: 'open_time' },
          { count: 2, id: '1w', title: '1w', type: 'open_time' }
        ]
      }
    ]
  };

  const dummyUrgencyProps = {
    alwaysExpanded: true,
    items:          [
      {
        count:      3,
        id:         3,
        title:      'Filter #3',
        grouped_by: 'urgency',
        nested:     [
          { count: 1, id: '1', title: '1', type: 'open_time' },
          { count: 2, id: '2', title: '2', type: 'open_time' }
        ]
      }
    ]
  };

  let node;

  function render(props) {
    node = ReactDOM.findDOMNode(renderInTicketsApp({}, <NestedList {...props} />));
  }

  it('should render list items', () => {
    render(dummyProps);
    const items = node.querySelectorAll('ul li a.item');
    expect(items.length).toEqual(5);
  });

  it('should render nested items', () => {
    render(dummyProps);
    const nested = node.querySelectorAll('ul.depth-1 li a.item');
    expect(nested.length).toEqual(2);
  });

  it('should render UrgencyList when there is item grouped by urgency', () => {
    spyOn(UrgencyList.prototype, 'render');
    render(dummyUrgencyProps);
    expect(UrgencyList.prototype.render).toHaveBeenCalled();
  });

  it('should not render UrgencyList when there is no items grouped by urgency', () => {
    spyOn(UrgencyList.prototype, 'render');
    render(dummyProps);
    expect(UrgencyList.prototype.render).not.toHaveBeenCalled();
  });
});
