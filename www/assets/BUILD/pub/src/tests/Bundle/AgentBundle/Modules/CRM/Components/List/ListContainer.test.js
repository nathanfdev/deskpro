// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderInCrmApp } from '../../crm.test-helper';

describe('CRM: ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List          = require('~Components/List').List;

  it('should render List component', () => {
    spyOn(List.prototype, 'render').and.callThrough();
    renderInCrmApp(0, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
