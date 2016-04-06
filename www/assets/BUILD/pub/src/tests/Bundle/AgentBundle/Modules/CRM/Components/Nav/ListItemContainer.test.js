// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav
// #define ~lists DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/Lists

jest.dontMock('~nav/ListItemContainer');

import React from 'react';
import { renderInCrmApp } from '../../crm.test-helper';

describe('CRM Navigation: ListItemContainer component', () => {
  const ListItemContainer = require('~nav/ListItemContainer').ListItemContainer;
  const ListItemStatefulContainer = require('~lists/ListItemStatefulContainer').ListItemStatefulContainer;

  function render() {
    return renderInCrmApp(
      {},
      <ListItemContainer>
        <div/>
      </ListItemContainer>
    );
  }

  it('should render a ListItemStatefulContainer', () => {
    spyOn(ListItemStatefulContainer.prototype, 'render').and.callThrough();
    render();
    expect(ListItemStatefulContainer.prototype.render).toHaveBeenCalled();
  });
});
