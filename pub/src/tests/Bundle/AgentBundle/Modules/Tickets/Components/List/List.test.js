// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List

jest.dontMock('~List/List');

import React from 'react';
import TestUtils from 'react-addons-test-utils';
import { renderInTicketsApp } from '../../tickets.test-helper';
import { fakeRecordStoreState } from 'Helpers/redux';

describe('List', () => {
  const ListFrameContainer  = require('~ListFrame/frame').ListFrameContainer;
  const List                = require('~List/List').List;
  const ControlBarContainer = require('~List/ControlBarContainer').ControlBarContainer;
  const ListCardViewContainer = require('~List/View/Card/ListCardViewContainer').ListCardViewContainer;
  const ListTableViewContainer = require('~List/View/Table/ListTableViewContainer').ListTableViewContainer;

  const fakeState = {
    RecordStores: {Tickets: {tickets: fakeRecordStoreState({}, {tickets: []})}}
  };

  const renderList = (viewMode = 'card') => {
    renderInTicketsApp(fakeState, <List elements={[]} viewMode={viewMode} loaded={true} />);
  };

  it('should render ListFrameContainer', () => {
    spyOn(ListFrameContainer.prototype, 'render').andCallThrough();
    renderList();
    expect(ListFrameContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render its control bar', () => {
    spyOn(ControlBarContainer.prototype, 'render').andCallThrough();
    renderList();
    expect(ControlBarContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ListCardViewContainer when the passed viewMode is "card"', () => {
    spyOn(ListCardViewContainer.prototype, 'render').andCallThrough();
    spyOn(ListTableViewContainer.prototype, 'render').andCallThrough();

    renderList('card');

    expect(ListCardViewContainer.prototype.render).toHaveBeenCalled();
    expect(ListTableViewContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ListTableViewContainer when the passed viewMode is "table"', () => {
    spyOn(ListCardViewContainer.prototype, 'render').andCallThrough();
    spyOn(ListTableViewContainer.prototype, 'render').andCallThrough();

    renderList('table');

    expect(ListTableViewContainer.prototype.render).toHaveBeenCalled();
    expect(ListCardViewContainer.prototype.render).not.toHaveBeenCalled();
  });
});
