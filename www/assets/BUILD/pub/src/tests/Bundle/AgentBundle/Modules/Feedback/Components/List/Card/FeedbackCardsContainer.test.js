// #define ~Card DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List/View/Card

jest.dontMock('~Card/FeedbackCardsContainer');

import React from 'react';
import { renderInFeedbackApp } from '../../../feedback.test-helper';
import { toImmutable } from 'Helpers';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

describe('Feedback: FeedbackCardsContainer', () => {
  const FeedbackCardsContainer = require('~Card/FeedbackCardsContainer').FeedbackCardsContainer;
  const FeedbackCard = require('~Card/FeedbackCard').FeedbackCard;
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
    renderInFeedbackApp(fakeState, <FeedbackCardsContainer/>);
  };

  it('should render FeedbackCard', () => {
    spyOn(FeedbackCard.prototype, 'render').and.callThrough();
    render();
    expect(FeedbackCard.prototype.render).toHaveBeenCalled();
  });
});
