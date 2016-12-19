// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs
// #define ~common DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame

jest.dontMock('~components/StarsTab');
jest.mock('~common/Lists/NestedList');

import React from 'react';
import { toImmutable } from 'Helpers';
import { renderInTicketsApp } from '../../../tickets.test-helper';

describe('Tickets Navigation: StarsTab component', () => {
  const StarsTab   = require('~components/StarsTab').StarsTab;
  const ListItemContainer = require('~components/ListItemContainer').ListItemContainer;

  function render() {
    const props = {
      starsCount: toImmutable([
        {count: 0, title: 'Star #1'},
        {count: 1, title: 'Star #2'},
        {count: 5, title: 'Star #3'}
      ])
    };

    return renderInTicketsApp({}, <StarsTab {...props} />);
  }

  it('should render ListItemContainer three times when there are three counts', () => {
    spyOn(ListItemContainer.prototype, 'render');
    render();
    expect(ListItemContainer.prototype.render).toHaveBeenCalledTimes(3);
  });
});
