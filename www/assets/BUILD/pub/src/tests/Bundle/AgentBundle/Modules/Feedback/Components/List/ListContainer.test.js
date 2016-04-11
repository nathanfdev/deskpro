// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.dontMock('~Components/ListContainer');

import React from 'react';
import { renderInFeedbackApp } from '../../feedback.test-helper';

describe('Feedback: ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List          = require('~Components/List').List;

  it('should render List component', () => {
    spyOn(List.prototype, 'render').and.callThrough();
    renderInFeedbackApp(0, <ListContainer />);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
