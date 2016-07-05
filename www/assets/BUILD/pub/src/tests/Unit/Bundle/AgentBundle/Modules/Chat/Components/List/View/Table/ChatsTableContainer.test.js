// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~ListFrame/index');
jest.dontMock('~List/View/Table/ChatsTableContainer');

import React from 'react';
import { renderChatsInRedux } from '../../../../chats.test-helper';

describe('ChatsTableContainer', () => {
  const ChatsTableContainer = require('~List/View/Table/ChatsTableContainer').ChatsTableContainer;
  const Row = require('~List/View/Table/Row').Row;
  const TableHeader = require('~List/View/Table/TableHeader').TableHeader;

  it('should render its header', () => {
    spyOn(TableHeader.prototype, 'render').and.callThrough();
    renderChatsInRedux(1, <ChatsTableContainer />);
    expect(TableHeader.prototype.render).toHaveBeenCalled();
  });

  it('should render 3 rows when passing 3 children', () => {
    spyOn(Row.prototype, 'render').and.callThrough();
    renderChatsInRedux(3, <ChatsTableContainer />);
    expect(Row.prototype.render.calls.count()).toEqual(3);
  });
});
