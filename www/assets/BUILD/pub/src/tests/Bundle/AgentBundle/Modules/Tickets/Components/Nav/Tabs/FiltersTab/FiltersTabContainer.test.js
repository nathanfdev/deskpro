// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab

jest.dontMock('~components/FiltersTabContainer');

import React from 'react';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

describe('Tickets Navigation: FiltersTabContainer component', () => {
  const FiltersTabContainer = require('~components/FiltersTabContainer').FiltersTabContainer;
  const FiltersTab          = require('~components/FiltersTab').FiltersTab;

  function render() {
    return renderInTicketsApp({}, <FiltersTabContainer />);
  }

  it('should render FiltersTab', () => {
    spyOn(FiltersTab.prototype, 'render');
    render();
    expect(FiltersTab.prototype.render).toHaveBeenCalled();
  });
});
