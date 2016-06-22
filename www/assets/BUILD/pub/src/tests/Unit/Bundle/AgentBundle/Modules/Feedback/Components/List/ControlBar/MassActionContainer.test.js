// #define ~MassActionBar DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List/ControlBar

jest.dontMock('~ControlBar/MassActionContainer');

import React from 'react';
import { renderInFeedbackApp } from '../../../feedback.test-helper';
import { toImmutable } from 'Helpers';

describe('Feedback: MassActionContainer', () => {
  const MassActionContainer    = require('~ControlBar/MassActionContainer').MassActionContainer;
  const MassActionBarContainer = require('~MassActionBar/MassActionBarContainer').MassActionBarContainer;
  const fakeState              = {
    Feedback: {
      list: toImmutable({ currentListParams: { navItem: { awaiting_validation: 1 } } }),
      nav:  toImmutable({ labels: [], statuses: { active: {}, closed: {}, hidden: {} } })
    }
  };

  const render = () => {
    renderInFeedbackApp(fakeState, <MassActionContainer />);
  };

  it('should render MassActionBarContainer', () => {
    spyOn(MassActionBarContainer.prototype, 'render').and.callThrough();
    render();
    expect(MassActionBarContainer.prototype.render).toHaveBeenCalled();
  });
});
