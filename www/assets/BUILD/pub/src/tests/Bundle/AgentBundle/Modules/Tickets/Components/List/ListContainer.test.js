// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Tickets/Selectors

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List = require('~Components/List').List;

  it('should render List component', () => {
    spyOn(List.prototype, 'render').and.callThrough();
    renderInTicketsApp(0, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
