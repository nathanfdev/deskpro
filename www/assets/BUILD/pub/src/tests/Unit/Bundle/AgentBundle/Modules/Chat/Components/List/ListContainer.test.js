// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderInChatApp } from '../../chat.test-helper';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List          = require('~Components/List').List;
  const Selectors     = require('~Selectors/list');

  it('should select viewMode and pagination from Chat.list state', () => {
    spyOn(Selectors, 'viewModeSelector');
    spyOn(Selectors, 'paginationSelector');

    renderInChatApp({}, <ListContainer />);

    expect(Selectors.viewModeSelector).toHaveBeenCalled();
    expect(Selectors.paginationSelector).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').and.callThrough();
    renderInChatApp({}, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
