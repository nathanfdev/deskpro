jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard');

import React from 'react';
import TestUtils from 'react-addons-test-utils';

describe('ChatCard', () => {
  const ChatCard = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard').ChatCard;

  it('should render a chat card', () => {
    const component = TestUtils.renderIntoDocument(<ChatCard chat={{}} />);
    const cards = TestUtils.scryRenderedDOMComponentsWithClass(component, 'chat-card');
    expect(cards.length).toEqual(1);
  });
});
