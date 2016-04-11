// #define ~MassActionBar DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/ControlBar

jest.dontMock('~ControlBar/MassActionContainer');

import React from 'react';
import { renderInCrmApp } from '../../../crm.test-helper';

describe('CRM: MassActionContainer', () => {
  const MassActionContainer    = require('~ControlBar/MassActionContainer').MassActionContainer;
  const MassActionBarContainer = require('~MassActionBar/MassActionBarContainer').MassActionBarContainer;
  const fakeState              = {};

  const render = () => {
    renderInCrmApp(fakeState, <MassActionContainer />);
  };

  it('should render MassActionBarContainer', () => {
    spyOn(MassActionBarContainer.prototype, 'render').and.callThrough();
    render();
    expect(MassActionBarContainer.prototype.render).toHaveBeenCalled();
  });
});
