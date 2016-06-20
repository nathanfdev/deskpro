// #define ~root DeskPRO/Bundle/AgentBundle/Modules/Feedback
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/Nav

jest.dontMock('~nav/NavContainer');

import React from 'react';
import { renderInFeedbackApp } from '../../feedback.test-helper';

describe('Feedback Navigation: NavContainer component', () => {
  const NavContainer = require('~nav/NavContainer').NavContainer;
  const Nav          = require('~nav/Nav').Nav;
  const actions      = require('~root/Actions/feedbackNavActions');
  const dispatch     = jasmine.createSpy('dispatch');

  function render() {
    return renderInFeedbackApp({}, <NavContainer />, dispatch);
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
