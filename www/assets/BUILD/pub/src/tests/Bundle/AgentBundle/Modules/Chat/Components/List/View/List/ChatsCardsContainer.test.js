// #define ~ListView DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List

jest.dontMock('~ListView/ChatsCardsContainer');

import React from 'react';

import { renderChatsInRedux } from '../../../../chats.test-helper';

describe('ChatsCardsContainer', () => {
  const ChatsCardsContainer = require('~ListView/ChatsCardsContainer').ChatsCardsContainer;
  const ChatCard = require('~ListView/ChatCard').ChatCard;

  it('should render 3 ChatCard elements when passing 3 children', () => {
    spyOn(ChatCard.prototype, 'render').andCallThrough();
    renderChatsInRedux(3, <ChatsCardsContainer />);
    expect(ChatCard.prototype.render.calls.length).toEqual(3);
  });
});
