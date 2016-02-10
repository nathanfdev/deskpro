// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Feedback/Selectors

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderFeedbackInRedux } from '../../feedback.test-helper';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List = require('~Components/List').List;
  const Selectors = require('~Selectors/list');

  it('should select isComments, isLoaded, viewMode and pagination from Chat.list state', () => {
    spyOn(Selectors, 'isCommentsSelector');
    spyOn(Selectors, 'currentViewModeSelector');
    spyOn(Selectors, 'isLoadedSelector');
    spyOn(Selectors, 'paginationSelector');

    renderChatsInRedux(0, <ListContainer />);

    expect(Selectors.isCommentsSelector).toHaveBeenCalled();
    expect(Selectors.currentViewModeSelector).toHaveBeenCalled();
    expect(Selectors.isLoadedSelector).toHaveBeenCalled();
    expect(Selectors.paginationSelector).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderFeedbackInRedux(0, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
