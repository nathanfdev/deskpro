// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderInRedux, fakeState, toImmutable } from 'Helpers/redux';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List = require('~Components/List').List;
  const Selectors = require('~Selectors/list');

  const state = fakeState({Chat: {list: toImmutable({})}});

  it('should select viewMode, loaded and pagination from Chat.list state', () => {
    spyOn(Selectors, 'viewModeSelector');
    spyOn(Selectors, 'loadedSelector');
    spyOn(Selectors, 'paginationSelector');

    renderInRedux(state, <ListContainer />);

    expect(Selectors.viewModeSelector).toHaveBeenCalled();
    expect(Selectors.loadedSelector).toHaveBeenCalled();
    expect(Selectors.paginationSelector).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderInRedux(state, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
