// #define ~Card DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List/View/Card

jest.dontMock('~Card/CommentCardsContainer');

import React from 'react';
import { toImmutable } from 'Helpers';
import { renderInFeedbackApp } from '../../../feedback.test-helper';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

describe('Feedback: CommentCardsContainer', () => {
  const CommentCardsContainer = require('~Card/CommentCardsContainer').CommentCardsContainer;
  const CommentCard = require('~Card/CommentCard').CommentCard;
  const fakeRecords = [{ id: 1 }, { id: 2 }, { id: 3 }];
  const fakeState = {
    RecordsStore: {
      store: toImmutable({
        Person: {
          collections: { feedback: [1] },
          records: mapKeyedFromArray(fakeRecords, 'id'),
          statuses: { loading: false, success: true }
        },
        Feedback: {
          collections: { feedback: [1] },
          records: mapKeyedFromArray(fakeRecords, 'id'),
          statuses: { loading: false, success: true }
        },
        FeedbackComment: {
          collections: { feedback: [1] },
          records: mapKeyedFromArray(fakeRecords, 'id'),
          statuses: { loading: false, success: true }
        }
      })
    },
    Feedback: {
      list: toImmutable({
        elements: [1],
        visibleFields: { card: [], table: [] }
      })
    }
  };

  const render = () => {
    renderInFeedbackApp(fakeState, <CommentCardsContainer/>);
  };

  it('should render FeedbackCard', () => {
    spyOn(CommentCard.prototype, 'render').and.callThrough();
    render();
    expect(CommentCard.prototype.render).toHaveBeenCalled();
  });
});
