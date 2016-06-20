// #define ~Nav DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/Nav


jest.dontMock('~Nav/CategoryTab');

import React from 'react';
import { toImmutable } from 'helpers';
import { renderInFeedbackApp } from '../../feedback.test-helper';

describe('Feedback: CategoryTab', () => {
  const CategoryTab       = require('~Nav/CategoryTab').CategoryTab;
  const ListItemContainer = require('~Nav/ListItemContainer').ListItemContainer;
  const fakeState         = {};
  const categories        = toImmutable({
    count:      100,
    grouped_by: 'custom_category',
    nested:     [
      { id: 1, count: 28, title: 'Linux', type: 'custom_category' },
      { id: 2, count: 18, title: 'Mac', type: 'custom_category' },
      { id: 3, count: 54, title: 'Windows', type: 'custom_category' }
    ]
  });

  const render = () => {
    renderInFeedbackApp(fakeState, <CategoryTab categories={categories} />);
  };

  it('should render ListItemContainer', () => {
    spyOn(ListItemContainer.prototype, 'render').and.callThrough();
    render();
    expect(ListItemContainer.prototype.render).toHaveBeenCalled();
  });
});
