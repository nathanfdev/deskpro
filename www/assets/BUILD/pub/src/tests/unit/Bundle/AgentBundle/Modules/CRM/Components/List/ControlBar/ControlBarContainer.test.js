// #define ~CommonControlBar DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/ControlBar

jest.dontMock('~ControlBar/ControlBarContainer');

import React from 'react';
import { renderInCrmApp } from '../../../crm.test-helper';

describe('CRM: ControlBarContainer', () => {
  const ControlBarContainer = require('~ControlBar/ControlBarContainer').ControlBarContainer;
  const ControlBar          = require('~CommonControlBar/ControlBar').ControlBar;

  const render = () => {
    renderInCrmApp({}, <ControlBarContainer />);
  };

  it('should render ControlBar', () => {
    spyOn(ControlBar.prototype, 'render').and.callThrough();
    render();
    expect(ControlBar.prototype.render).toHaveBeenCalled();
  });
});
