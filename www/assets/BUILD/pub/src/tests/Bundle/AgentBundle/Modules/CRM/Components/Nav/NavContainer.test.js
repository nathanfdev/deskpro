// #define ~root DeskPRO/Bundle/AgentBundle/Modules/CRM
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav

jest.dontMock('~nav/NavContainer');

import React from 'react';
import { renderInCrmApp } from '../../crm.test-helper';

describe('CRM Navigation: NavContainer component', () => {
  const NavContainer = require('~nav/NavContainer').NavContainer;
  const Nav = require('~nav/Nav').Nav;
  const actions = require('~root/Actions/crmNavActions');
  const dispatch = jasmine.createSpy('dispatch');

  function render() {
    return renderInCrmApp({}, <NavContainer/>, dispatch);
  }

  it('should render Nav', () => {
    spyOn(Nav.prototype, 'render');
    render();
    expect(Nav.prototype.render).toHaveBeenCalled();
  });

  it('should dispatch the initialLoad() event', () => {
    dispatch.calls.reset();
    spyOn(actions, 'initialLoad').and.callThrough();

    render();
    expect(actions.initialLoad).toHaveBeenCalled();
  });
});
