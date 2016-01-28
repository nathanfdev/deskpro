// #define ~root DeskPRO/Bundle/AgentBundle/Modules/Tickets
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav

jest.dontMock('~nav/NavContainer');

import React from 'react';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('Ticket: NavContainer component', () => {
  const NavContainer = require('~nav/NavContainer').NavContainer;
  const Nav          = require('~nav/Nav').Nav;
  const actions      = require('~root/Actions/navActions');
  const dispatch     = jasmine.createSpy('dispatch');

  function render() {
    return renderInTicketsApp(<NavContainer />, dispatch);
  }

  it('should render Nav', () => {
    spyOn(Nav.prototype, 'render');
    render();
    expect(Nav.prototype.render).toHaveBeenCalled();
  });

  it('should dispatch the initialLoad() event', () => {
    dispatch.reset();
    spyOn(actions, 'initialLoad').andCallThrough();

    render();

    expect(dispatch).toHaveBeenCalledWith(jasmine.objectContaining({type: actions.initialLoad.originalValue.type}));
    expect(actions.initialLoad).toHaveBeenCalled();
  });
});
