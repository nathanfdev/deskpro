// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~Pagination DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List

jest.dontMock('~List/List');

import React from 'react';
import { renderInCrmApp } from '../../crm.test-helper';
import { toImmutable } from 'Helpers';

describe('CRM: List', () => {
  const ListFrameContainer = require('~ListFrame/frame').ListFrameContainer;
  const ListFrameMenu = require('~ListFrame/ListFrameMenu').ListFrameMenu;
  const ListFrameContents = require('~ListFrame/ListFrameContents').ListFrameContents;
  const List = require('~List/List').List;
  const PaginationBoxView = require('~Pagination/PaginationBoxView').PaginationBoxView;
  const ControlBarContainer = require('~List/ControlBar/ControlBarContainer').ControlBarContainer;
  const MassActionContainer = require('~List/ControlBar/MassActionContainer').MassActionContainer;
  const CrmCardContainer = require('~List/View/Card/CrmCardContainer').CrmCardContainer;
  const CrmTableContainer = require('~List/View/Table/CrmTableContainer').CrmTableContainer;
  const fakeState = {};

  const renderList = (viewMode = 'card', selected = toImmutable([]), isComments = false, pagination = null) => {

    const emptyList = toImmutable([]);

    renderInCrmApp(
      fakeState,
      <List
        elements={[]}
        selected={selected}
        currentViewMode={viewMode}
        isComments={isComments}
        pagination={pagination}
        peopleFields={emptyList}
        orgFields={emptyList}
        loaded
      />
    );
  };

  it('should render ListFrameContainer', () => {
    spyOn(ListFrameContainer.prototype, 'render').and.callThrough();
    renderList();
    expect(ListFrameContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ListFrameMenu', () => {
    spyOn(ListFrameMenu.prototype, 'render').and.callThrough();
    renderList();
    expect(ListFrameMenu.prototype.render).toHaveBeenCalled();
  });

  it('should render its control bar if selected is empty', () => {
    spyOn(ControlBarContainer.prototype, 'render').and.callThrough();
    spyOn(MassActionContainer.prototype, 'render').and.callThrough();
    renderList();
    expect(ControlBarContainer.prototype.render).toHaveBeenCalled();
    expect(MassActionContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render its mass actions bar if selected is not empty', () => {
    spyOn(ControlBarContainer.prototype, 'render').and.callThrough();
    spyOn(MassActionContainer.prototype, 'render').and.callThrough();
    renderList('card', toImmutable([1, 2]));
    expect(MassActionContainer.prototype.render).toHaveBeenCalled();
    expect(ControlBarContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ListFrameContents', () => {
    spyOn(ListFrameContents.prototype, 'render').and.callThrough();
    renderList();
    expect(ListFrameContents.prototype.render).toHaveBeenCalled();
  });

  it('should render FeedbackCardsContainer when the passed viewMode is "card" and isComments is false', () => {
    spyOn(CrmCardContainer.prototype, 'render').and.callThrough();
    spyOn(CrmTableContainer.prototype, 'render').and.callThrough();

    renderList('card');

    expect(CrmCardContainer.prototype.render).toHaveBeenCalled();
    expect(CrmTableContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ListTableViewContainer when the passed viewMode is "table"', () => {
    spyOn(CrmCardContainer.prototype, 'render').and.callThrough();
    spyOn(CrmTableContainer.prototype, 'render').and.callThrough();

    renderList('table');

    expect(CrmTableContainer.prototype.render).toHaveBeenCalled();
    expect(CrmCardContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('shouldn\'t render PaginationBoxView when the pagination not passed', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();

    renderList();

    expect(PaginationBoxView.prototype.render).not.toHaveBeenCalled();
  });

  it('shouldn\'t render PaginationBoxView when the pagination passed with total_pages === 1', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();

    renderList('card', toImmutable([]), false, toImmutable({total_pages: 1}));

    expect(PaginationBoxView.prototype.render).not.toHaveBeenCalled();
  });

  it('should render PaginationBoxView when the pagination passed with total_pages > 1', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();

    renderList('card', toImmutable([]), false, toImmutable({total_pages: 2}));

    expect(PaginationBoxView.prototype.render).toHaveBeenCalled();
  });
});
