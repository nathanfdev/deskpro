// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.disableAutomock();

import React from 'react';
import TestUtils from 'react-dom/test-utils';
import { renderInFeedbackApp } from '../../feedback.test-helper';

describe('Feedback: ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;

  it('<ListContainer />', () => {
    const component = renderInFeedbackApp(0, <ListContainer />);
    const items     = TestUtils.findAllInRenderedTree(
      component,
      element => TestUtils.isCompositeComponentWithType(element, ListContainer)
    );
    expect(items.length).toEqual(1);
  });
});
