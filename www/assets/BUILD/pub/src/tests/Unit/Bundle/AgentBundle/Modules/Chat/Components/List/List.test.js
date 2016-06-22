// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~ListFrame/frame');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ListFrame/ListFrameMenu');
jest.dontMock('~List/List');
jest.dontMock('~List/View/List/ChatsCardsContainer');
jest.dontMock('~List/View/Table/ChatsTableContainer');
jest.dontMock('~List/ControlBar/ControlBarContainer');

import React from 'react';
import { toImmutable } from 'Helpers';
import { renderChatsInRedux } from '../../chats.test-helper';

describe('List', () => {
  const ListFrameContainer  = require('~ListFrame/frame').ListFrameContainer;
  const List                = require('~List/List').List;
  const ControlBarContainer = require('~List/ControlBar/ControlBarContainer').ControlBarContainer;
  const ChatsCardsContainer = require('~List/View/List/ChatsCardsContainer').ChatsCardsContainer;
  const ChatsTableContainer = require('~List/View/Table/ChatsTableContainer').ChatsTableContainer;

  const renderList = (viewMode = 'card', pagination = null) => {
    const emptyList = toImmutable([]);
    renderChatsInRedux(3,
      <List
        currentListParams={toImmutable({})}
        elements={[]}
        viewMode={viewMode}
        loaded
        pagination={pagination}
        cardFields={emptyList}
        tableFields={emptyList}
      />);
  };

  it('should render ListFrameContainer', () => {
    spyOn(ListFrameContainer.prototype, 'render').and.callThrough();
    renderList();
    expect(ListFrameContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render its control bar', () => {
    spyOn(ControlBarContainer.prototype, 'render').and.callThrough();
    renderList();
    expect(ControlBarContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ChatsCardsContainer when the passed viewMode is "card"', () => {
    spyOn(ChatsCardsContainer.prototype, 'render').and.callThrough();
    spyOn(ChatsTableContainer.prototype, 'render').and.callThrough();

    renderList('card');

    expect(ChatsCardsContainer.prototype.render).toHaveBeenCalled();
    expect(ChatsTableContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ChatsTableContainer when the passed viewMode is "table"', () => {
    spyOn(ChatsCardsContainer.prototype, 'render').and.callThrough();
    spyOn(ChatsTableContainer.prototype, 'render').and.callThrough();

    renderList('table');

    expect(ChatsTableContainer.prototype.render).toHaveBeenCalled();
    expect(ChatsCardsContainer.prototype.render).not.toHaveBeenCalled();
  });
});
