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
      starsCount: toImmutable([{ count: 0, title: 'Test Star' }])
    };

    return renderInTicketsApp({}, <StarsTab {...props} />);
  }

  it('should render NestedList', () => {
    spyOn(ListItemContainer.prototype, 'render');
    render();
    expect(ListItemContainer.prototype.render).toHaveBeenCalled();
  });
});
