// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components

jest.dontMock('~components/TicketsApp');

import React from 'react';
import { renderInRedux, fakeState } from 'Helpers';

describe('Ticket: TicketsApp component', () => {
  const TicketsApp    = require('~components/TicketsApp').TicketsApp;
  const NavContainer  = require('~components/Nav/NavContainer').NavContainer;
  const ListContainer = require('~components/List/ListContainer').ListContainer;

  function render() {
    return renderInRedux(fakeState({}), <TicketsApp />);
  }

  it('should render NavContainer', () => {
    spyOn(NavContainer.prototype, 'render');
    render();
    expect(NavContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ListContainer', () => {
    spyOn(ListContainer.prototype, 'render');
    render();
    expect(ListContainer.prototype.render).toHaveBeenCalled();
  });
});
