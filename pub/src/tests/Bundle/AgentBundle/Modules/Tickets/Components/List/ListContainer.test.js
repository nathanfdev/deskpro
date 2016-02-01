// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Tickets/Selectors

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List = require('~Components/List').List;
  const Selectors = require('~Selectors/list');

  it('should select viewMode, isLoaded and pagination from Ticket.list state', () => {
    spyOn(Selectors, 'viewModeSelector');
    spyOn(Selectors, 'isLoadedSelector');
    spyOn(Selectors, 'paginationSelector');

    renderInTicketsApp(0, <ListContainer />);

    expect(Selectors.viewModeSelector).toHaveBeenCalled();
    expect(Selectors.isLoadedSelector).toHaveBeenCalled();
    expect(Selectors.paginationSelector).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderInTicketsApp(0, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
