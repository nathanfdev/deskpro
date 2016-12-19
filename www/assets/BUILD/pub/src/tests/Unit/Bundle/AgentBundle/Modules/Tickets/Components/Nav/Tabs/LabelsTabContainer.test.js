// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs

jest.dontMock('~components/LabelsTabContainer');

import React from 'react';
import { renderInTicketsApp } from '../../../tickets.test-helper';

describe('Tickets Navigation: LabelsTabContainer component', () => {
  const LabelsTabContainer = require('~components/LabelsTabContainer').LabelsTabContainer;
  const LabelsTab          = require('~components/LabelsTab').LabelsTab;

  function render() {
    return renderInTicketsApp({}, <LabelsTabContainer />);
  }

  it('should render LabelsTab', () => {
    spyOn(LabelsTab.prototype, 'render');
    render();
    expect(LabelsTab.prototype.render).toHaveBeenCalled();
  });
});
