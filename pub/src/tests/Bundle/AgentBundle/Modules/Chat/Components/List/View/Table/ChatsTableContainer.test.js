// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~ListFrame/index');
jest.dontMock('~List/View/Table/ChatsTableContainer');
jest.dontMock('~List/View/Table/TableHeader');

import React from 'react';
import { renderInRedux, fakeState, fakeRecordStoreState, toImmutable } from 'Helpers/redux';

describe('ChatsTableContainer', () => {
  const ChatsTableContainer = require('~List/View/Table/ChatsTableContainer').ChatsTableContainer;
  const Row = require('~List/View/Table/Row').Row;
  const TableHeader = require('~List/View/Table/TableHeader').TableHeader;

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
      <ChatsTableContainer />
    );
  };

  it('should render its header', () => {
    spyOn(TableHeader.prototype, 'render').andCallThrough();
    render();
    expect(TableHeader.prototype.render).toHaveBeenCalled();
  });

  it('should render 3 rows when passing 3 children', () => {
    spyOn(Row.prototype, 'render').andCallThrough();
    render(3);
    expect(Row.prototype.render.calls.length).toEqual(3);
  });
});
