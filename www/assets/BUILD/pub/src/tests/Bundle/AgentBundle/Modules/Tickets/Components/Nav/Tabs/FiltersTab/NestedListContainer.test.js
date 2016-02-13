// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab

jest.dontMock('~components/NestedListContainer');
jest.dontMock('~components/ListItemContainer');

import React from 'react';
import ReactDOM from 'react-dom';
import TestUtils from 'react-addons-test-utils';
import { toImmutable } from 'Helpers/redux';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

describe('Tickets Navigation: NestedListContainer component', () => {
  const NestedListContainer = require('~components/NestedListContainer').NestedListContainer;
  const UrgencyList = require('~components/UrgencyList').UrgencyList;

  const dummyProps = {
    items: [
      {count: 1, id: 1, title: 'Filter #1', nested: []},
      {count: 2, id: 2, title: 'Filter #2', nested: []},
      {count: 3, id: 3, title: 'Filter #3', grouped_by: 'open_time', nested: [
        {count: 1, id: '1m', title: '1m', type: 'open_time'},
        {count: 2, id: '1w', title: '1w', type: 'open_time'}
      ]}
    ],
    alwaysExpanded: true
  };
  const dummyUrgencyProps = {
    items: [
      {count: 3, id: 3, title: 'Filter #3', grouped_by: 'urgency', nested: [
        {count: 1, id: '1', title: '1', type: 'open_time'},
        {count: 2, id: '2', title: '2', type: 'open_time'}
      ]}
    ],
    alwaysExpanded: true
  };

  let component, node;
  function render(props) {
    component = renderInTicketsApp({}, <NestedListContainer {...props} />);
    node = ReactDOM.findDOMNode(component);
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
