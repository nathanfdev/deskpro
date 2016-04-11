jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard');
jest.mock('react-intl');

import React from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { toImmutable } from 'Helpers';

describe('ChatCard', () => {
  const ChatCard = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard').ChatCard;

  it('should render chat subject', () => {
    const props = {
      chat:           toImmutable({ subject: 'Test chat subject' }),
      department:     toImmutable({}),
      toggleSelected: () => null
    };
    const html  = renderToStaticMarkup(<ChatCard {...props} />);
    expect(html).toContain('Test chat subject');
  });
});
