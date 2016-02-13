// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderChatsInRedux } from '../../chats.test-helper';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List = require('~Components/List').List;
  const Selectors = require('~Selectors/list');

  it('should select viewMode, isLoaded and pagination from Chat.list state', () => {
    spyOn(Selectors, 'viewModeSelector');
    spyOn(Selectors, 'isLoadedSelector');
    spyOn(Selectors, 'paginationSelector');

    renderChatsInRedux(0, <ListContainer />);

    expect(Selectors.viewModeSelector).toHaveBeenCalled();
    expect(Selectors.isLoadedSelector).toHaveBeenCalled();
    expect(Selectors.paginationSelector).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderChatsInRedux(0, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
