// #define ~ListView DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List

jest.dontMock('~ListView/ChatsCardsContainer');

import React from 'react';
import { renderInChatApp } from '../../../../chat.test-helper';
import { fakeRecordsStoreRequest } from 'Helpers';

describe('ChatsCardsContainer', () => {
  const ChatsCardsContainer = require('~ListView/ChatsCardsContainer').ChatsCardsContainer;
  const ChatCard = require('~ListView/ChatCard').ChatCard;

  it('should render 3 ChatCard elements when passing 3 children', () => {
    spyOn(ChatCard.prototype, 'render').and.callThrough();
    renderInChatApp(
      fakeRecordsStoreRequest('UserChat', 'chats', [{id: 1}, {id: 2}, {id: 3}]),
      <ChatsCardsContainer />
    );
    expect(ChatCard.prototype.render.calls.count()).toEqual(3);
  });
});
