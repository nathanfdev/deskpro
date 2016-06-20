// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~Pagination DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.dontMock('~List/List');

import React from 'react';
import { renderInFeedbackApp } from '../../feedback.test-helper';
import { toImmutable } from 'helpers';

describe('Feedback: List', () => {
  const ListFrameContainer     = require('~ListFrame/frame').ListFrameContainer;
  const ListFrameMenu          = require('~ListFrame/ListFrameMenu').ListFrameMenu;
  const ListFrameContents      = require('~ListFrame/ListFrameContents').ListFrameContents;
  const List                   = require('~List/List').List;
  const PaginationBoxView      = require('~Pagination/PaginationBoxView').PaginationBoxView;
  const ControlBarContainer    = require('~List/ControlBar/ControlBarContainer').ControlBarContainer;
  const MassActionContainer    = require('~List/ControlBar/MassActionContainer').MassActionContainer;
  const FeedbackCardsContainer = require('~List/View/Card/FeedbackCardsContainer').FeedbackCardsContainer;
  const CommentCardsContainer  = require('~List/View/Card/CommentCardsContainer').CommentCardsContainer;
  const FeedbackTableContainer = require('~List/View/Table/FeedbackTableContainer').FeedbackTableContainer;
  const CommentTableContainer  = require('~List/View/Table/CommentTableContainer').CommentTableContainer;
  const fakeState              = {};

  const renderList = (viewMode = 'card', selected = toImmutable([]), isComments = false, pagination = null) => {
    const emptyList = toImmutable([]);
    const func      = () => {
    };
    renderInFeedbackApp(
      fakeState,
      <List
        currentListParams={toImmutable({})}
        elements={emptyList}
        selected={selected}
        currentViewMode={viewMode}
        isComments={isComments}
        pagination={pagination}
        fields={toImmutable({ feedback: { card: [1, 2], table: [3, 4] }, comments: { card: [1, 2], table: [3, 4] } })}
        toggleSelected={func}
        handlePageClick={func}
        isLoaded
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
    spyOn(FeedbackCardsContainer.prototype, 'render').and.callThrough();
    spyOn(FeedbackTableContainer.prototype, 'render').and.callThrough();

    renderList('card');

    expect(FeedbackCardsContainer.prototype.render).toHaveBeenCalled();
    expect(FeedbackTableContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render ListTableViewContainer when the passed viewMode is "table"', () => {
    spyOn(FeedbackCardsContainer.prototype, 'render').and.callThrough();
    spyOn(FeedbackTableContainer.prototype, 'render').and.callThrough();

    renderList('table');

    expect(FeedbackTableContainer.prototype.render).toHaveBeenCalled();
    expect(FeedbackCardsContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render FeedbackCommentsCardsContainer when the passed viewMode is "card" and isComments is true', () => {
    spyOn(CommentCardsContainer.prototype, 'render').and.callThrough();
    spyOn(FeedbackTableContainer.prototype, 'render').and.callThrough();

    renderList('card', toImmutable([]), true);

    expect(CommentCardsContainer.prototype.render).toHaveBeenCalled();
    expect(FeedbackTableContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('should render FeedbackCommentTableContainer when the passed viewMode is "table"  and isComments is true', () => {
    spyOn(CommentCardsContainer.prototype, 'render').and.callThrough();
    spyOn(CommentTableContainer.prototype, 'render').and.callThrough();

    renderList('table', toImmutable([]), true);

    expect(CommentTableContainer.prototype.render).toHaveBeenCalled();
    expect(CommentCardsContainer.prototype.render).not.toHaveBeenCalled();
  });

  it('shouldn\'t render PaginationBoxView when the pagination not passed', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();

    renderList();

    expect(PaginationBoxView.prototype.render).not.toHaveBeenCalled();
  });

  it('shouldn\'t render PaginationBoxView when the pagination passed with total_pages === 1', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();

    renderList('card', toImmutable([]), false, toImmutable({ total_pages: 1 }));

    expect(PaginationBoxView.prototype.render).not.toHaveBeenCalled();
  });

  it('should render PaginationBoxView when the pagination passed with total_pages > 1', () => {
    spyOn(PaginationBoxView.prototype, 'render').and.callThrough();

    renderList('card', toImmutable([]), false, toImmutable({ total_pages: 2 }));

    expect(PaginationBoxView.prototype.render).toHaveBeenCalled();
  });
});
