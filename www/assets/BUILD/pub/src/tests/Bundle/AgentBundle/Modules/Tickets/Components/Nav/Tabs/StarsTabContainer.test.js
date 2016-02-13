// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs

jest.dontMock('~components/StarsTabContainer');

import React from 'react';
import { renderInTicketsApp } from '../../../tickets.test-helper';

describe('Tickets Navigation: StarsTabContainer component', () => {
  const StarsTabContainer = require('~components/StarsTabContainer').StarsTabContainer;
  const StarsTab          = require('~components/StarsTab').StarsTab;

  function render() {
    return renderInTicketsApp({}, <StarsTabContainer />);
  }

  it('should render StarsTab', () => {
    spyOn(StarsTab.prototype, 'render');
    render();
    expect(StarsTab.prototype.render).toHaveBeenCalled();
  });
});
