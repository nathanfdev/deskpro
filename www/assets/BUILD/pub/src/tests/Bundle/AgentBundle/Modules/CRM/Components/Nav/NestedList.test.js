// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav

jest.dontMock('~nav/NestedList');

import React from 'react';
import { renderInCrmApp } from '../../crm.test-helper';

describe('CRM Navigation: ListItemContainer component', () => {
  const NestedList        = require('~nav/NestedList').NestedList;
  const ListItemContainer = require('~nav/ListItemContainer').ListItemContainer;
  const fakeRecords       = [{ id: 1 }, { id: 2 }, { id: 3 }];

  function render() {
    return renderInCrmApp({}, <NestedList items={fakeRecords} />);
  }

  it('should render a ListItemContainer', () => {
    spyOn(ListItemContainer.prototype, 'render').and.callThrough();
    render();
    expect(ListItemContainer.prototype.render).toHaveBeenCalled();
  });
});
