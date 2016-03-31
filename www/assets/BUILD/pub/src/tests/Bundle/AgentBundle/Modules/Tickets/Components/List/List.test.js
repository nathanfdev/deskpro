// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List

jest.dontMock('~List/List');

import React from 'react';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('List', () => {
  const ListFrameContainer = require('~ListFrame/frame').ListFrameContainer;
  const List = require('~List/List').List;
  const ControlBarContainer = require('~List/ControlBarContainer').ControlBarContainer;
  const ListCardViewContainer = require('~List/View/Card/ListCardViewContainer').ListCardViewContainer;
  const ListTableViewContainer = require('~List/View/Table/ListTableViewContainer').ListTableViewContainer;

  const fakeState = {};

  const renderList = (viewMode = 'card') => {
    renderInTicketsApp(fakeState, <List elements={[]} selected={[]} viewMode={viewMode} loaded/>);
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

  it('should render ListCardViewContainer when the passed viewMode is "card"', () => {
    spyOn(ListCardViewContainer.prototype, 'render').and.callThrough();
    spyOn(ListTableViewContainer.prototype, 'render').and.callThrough();

    renderList('card');

    expect(ListCardViewContainer.prototype.render).toHaveBeenCalled();
    expect(ListTableViewContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ListTableViewContainer when the passed viewMode is "table"', () => {
    spyOn(ListCardViewContainer.prototype, 'render').and.callThrough();
    spyOn(ListTableViewContainer.prototype, 'render').and.callThrough();

    renderList('table');

    expect(ListTableViewContainer.prototype.render).toHaveBeenCalled();
    expect(ListCardViewContainer.prototype.render).not.toHaveBeenCalled();
  });
});
