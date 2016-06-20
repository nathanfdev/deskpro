// #define ~Card DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List/View/Card
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Feedback/Selectors

jest.dontMock('~Card/FeedbackCardsContainer');
jest.dontMock('~Card/FeedbackCard');
jest.dontMock('~Card/FeedbackCards');

import React from 'react';
import { renderInFeedbackApp, fakeFeedbackState } from '../../../feedback.test-helper';

describe('Feedback: FeedbackCardsContainer', () => {
  const FeedbackCardsContainer = require('~Card/FeedbackCardsContainer').FeedbackCardsContainer;
  const FeedbackCard           = require('~Card/FeedbackCard').FeedbackCard;
  const elementsSelector       = require('~Selectors/list').idsSelector;
  const cardFieldsSelector     = require('~Selectors/list').cardFieldsSelector;
  const tableFieldsSelector    = require('~Selectors/list').tableFieldsSelector;
  const fakeState              = fakeFeedbackState();

  const render = () => {
    const fields = cardFieldsSelector(fakeState);
    const elements = elementsSelector(fakeState);
    const toggle = () => {};
    renderInFeedbackApp(
      fakeState,
      <FeedbackCardsContainer fields={fields} elements={elements} toggleSelected={toggle} />
    );
  };

  it('should render list of the FeedbackCard', () => {
    spyOn(FeedbackCard.prototype, 'render').and.callThrough();
    render();
    expect(FeedbackCard.prototype.render).toHaveBeenCalled();
  });
});
