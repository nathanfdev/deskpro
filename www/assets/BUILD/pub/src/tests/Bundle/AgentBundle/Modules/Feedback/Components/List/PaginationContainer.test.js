// #define ~Pagination DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.dontMock('~List/PaginationContainer');

import React from 'react';
import { renderInFeedbackApp } from '../../feedback.test-helper';
import { toImmutable } from 'Helpers';

describe('Feedback: PaginationContainer', () => {
  const PaginationContainer = require('~List/PaginationContainer').PaginationContainer;
  const PaginationBoxView   = require('~Pagination/PaginationBoxView').PaginationBoxView;

  const fakeState = {
    Feedback: { list: toImmutable({ pagination: { total_pages: 2, current_page: 1 } }) }
  };

  const render = () => {
    renderInFeedbackApp(fakeState, <PaginationContainer />);
  };

  it('should render PaginationBoxView', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();
    render();
    expect(PaginationBoxView.prototype.render).toHaveBeenCalled();
  });
});
