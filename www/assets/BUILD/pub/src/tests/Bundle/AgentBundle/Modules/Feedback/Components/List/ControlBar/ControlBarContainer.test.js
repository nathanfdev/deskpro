// #define ~CommonControlBar DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List/ControlBar

jest.dontMock('~ControlBar/ControlBarContainer');

import React from 'react';
import { renderInFeedbackApp } from '../../../feedback.test-helper';

describe('Feedback: ControlBarContainer', () => {
  const ControlBarContainer = require('~ControlBar/ControlBarContainer').ControlBarContainer;
  const ControlBar = require('~CommonControlBar/ControlBar').ControlBar;
  const fakeState = {};

  const render = () => {
    renderInFeedbackApp(fakeState, <ControlBarContainer/>);
  };

  it('should render ControlBar', () => {
    spyOn(ControlBar.prototype, 'render').and.callThrough();
    render();
    expect(ControlBar.prototype.render).toHaveBeenCalled();
  });
});
