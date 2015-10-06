// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~ControlBar DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar

jest.dontMock('~ListFrame/ControlBar');
jest.dontMock('~ListFrame/index');
jest.dontMock('~ControlBar/ChatsListControlBar');

import React from 'react';
import TestUtils from 'react-addons-test-utils';

describe('ChatsListControlBar', () => {
  const ChatsListControlBar = require('~ControlBar/ChatsListControlBar').ChatsListControlBar;
  const OrderByContainer = require('~ControlBar/OrderByContainer').OrderByContainer;
  const ViewSwitcherContainer = require('~ControlBar/ViewSwitcherContainer').ViewSwitcherContainer;

  it('should render OrderByContainer', () => {
    spyOn(OrderByContainer.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsListControlBar />);
    expect(OrderByContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ViewSwitcherContainer', () => {
    spyOn(ViewSwitcherContainer.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsListControlBar />);
    expect(ViewSwitcherContainer.prototype.render).toHaveBeenCalled();
  });
});
