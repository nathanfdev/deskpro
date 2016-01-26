// #define ~ListView DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List

jest.dontMock('~ListView/ChatsCardsContainer');

import React from 'react';

import { renderInRedux, fakeState, fakeRecordStoreState, toImmutable } from 'Helpers/redux';

describe('ChatsCardsContainer', () => {
  const ChatsCardsContainer = require('~ListView/ChatsCardsContainer').ChatsCardsContainer;
  const ChatCard = require('~ListView/ChatCard').ChatCard;

  const render = (num = 0) => {
    const records = {};
    const ids = [];
    for (let i = 1; i <= num; i++) {
      const id = '' + i;
      records[i] = {id};
      ids.push(id);
    }

    renderInRedux(
      fakeState({
        Chat: {list: toImmutable({elements: ids})},
        RecordStores: {Chat: {chats: fakeRecordStoreState(records, {chats: ids})}}
      }),
      <ChatsCardsContainer />
    );
  };

  it('should render 3 ChatCard elements when passing 3 children', () => {
    spyOn(ChatCard.prototype, 'render').andCallThrough();
    render(3);
    expect(ChatCard.prototype.render.calls.length).toEqual(3);
  });
});
