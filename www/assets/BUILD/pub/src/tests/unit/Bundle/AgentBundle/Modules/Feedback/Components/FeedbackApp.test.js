// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components

jest.dontMock('~components/FeedbackApp');

import React from 'react';
import { renderInRedux, fakeState } from 'helpers';

describe('Feedback: FeedbackApp component', () => {
  const FeedbackApp   = require('~components/FeedbackApp').FeedbackApp;
  const NavContainer  = require('~components/Nav/NavContainer').NavContainer;
  const ListContainer = require('~components/List/ListContainer').ListContainer;

  function render() {
    return renderInRedux(fakeState({}), <FeedbackApp />);
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
