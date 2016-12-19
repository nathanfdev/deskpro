// #define ~ListFrame DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame
// #define ~Pagination DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination
// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.dontMock('~List/List');

import React from 'react';
import { toImmutable } from 'Helpers';
import sd from 'skin-deep';

let tree;

describe('Feedback: List', () => {
  const List      = require('~List/List').List;
  const emptyList = toImmutable([]);
  const func      = () => {
  };

  const props = {
    selected:          emptyList,
    isComments:        false,
    pagination:        emptyList,
    currentListParams: emptyList,
    elements:          emptyList,
    currentViewMode:   'card',
    fields:            toImmutable({
      feedback: { card: [1, 2], table: [3, 4] },
      comments: { card: [1, 2], table: [3, 4] }
    }),

    toggleSelected:  func,
    handlePageClick: func,
    isLoaded:        true
  };
  beforeEach(() => {
    tree = sd.shallowRender(<List {...props} />);
  });

  it('should contain ListFrameContainer', () => {
    expect(tree.subTree('ListFrameMenu')).toBeTruthy();
  });

  it('should contain ListFrameContents', () => {
    expect(tree.subTree('ListFrameContents')).toBeTruthy();
  });

  it('should contain ControlBarContainer and should not contain MassActionContainer if selected is empty', () => {
    expect(tree.subTree('ListFrameMenu').subTree('Connect(ControlBarContainer)')).toBeTruthy();
    expect(tree.subTree('ListFrameMenu').subTree('Connect(MassActionContainer)')).toBeFalsy();
  });

  it('should contain SaveAsCsv', () => {
    expect(tree.subTree('ListFrameContents').subTree('SaveAsCsv')).toBeTruthy();
  });

  it('should not contain ControlBarContainer and should contain MassActionContainer if selected is not empty', () => {
    const newProps = Object.assign({}, props, { selected: toImmutable([1, 2, 3]) });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameMenu').subTree('Connect(MassActionContainer)')).toBeTruthy();
    expect(tree.subTree('ListFrameMenu').subTree('Connect(ControlBarContainer)')).toBeFalsy();
  });

  it('should contain FeedbackCardsContainer and should not contain FeedbackTableContainer if currentViewMode is card', () => {
    const newProps = Object.assign({}, props, { currentViewMode: 'card', elements: toImmutable([1, 2]) });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameContents').subTree('Connect(FeedbackCardsContainer)')).toBeTruthy();
    expect(tree.subTree('ListFrameContents').subTree('Connect(InjectIntl(FeedbackTableContainer))')).toBeFalsy();
  });

  it('should not contain FeedbackCardsContainer and should contain FeedbackTableContainer if currentViewMode is table', () => {
    const newProps = Object.assign({}, props, { currentViewMode: 'table', elements: toImmutable([1, 2]) });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameContents').subTree('Connect(InjectIntl(FeedbackTableContainer))')).toBeTruthy();
    expect(tree.subTree('ListFrameContents').subTree('Connect(FeedbackCardsContainer)')).toBeFalsy();
  });

  it('should contain CommentCardsContainer and should not contain CommentTableContainer if currentViewMode is card and isComments is true', () => {
    const newProps = Object.assign({}, props, {
      currentViewMode: 'card',
      isComments:      true,
      elements:        toImmutable([1, 2])
    });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameContents').subTree('Connect(CommentCardsContainer)')).toBeTruthy();
    expect(tree.subTree('ListFrameContents').subTree('Connect(InjectIntl(CommentTableContainer))')).toBeFalsy();
  });

  it('should not contain CommentCardsContainer and should contain CommentTableContainer if currentViewMode is table and isComments is true', () => {
    const newProps = Object.assign({}, props, {
      currentViewMode: 'table',
      isComments:      true,
      elements:        toImmutable([1, 2])
    });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameContents').subTree('Connect(InjectIntl(CommentTableContainer))')).toBeTruthy();
    expect(tree.subTree('ListFrameContents').subTree('Connect(CommentCardsContainer)')).toBeFalsy();
  });

  it('should contain PaginationBoxView if the pagination passed with total_pages > 1', () => {
    const newProps = Object.assign({}, props, { pagination: toImmutable({ total_pages: 2 }) });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameContents').subTree('PaginationBoxView')).toBeTruthy();
  });

  it('should not contain PaginationBoxView if pagination is not passed', () => {
    expect(tree.subTree('ListFrameContents').subTree('PaginationBoxView')).toBeFalsy();
  });

  it('should not contain PaginationBoxView if the pagination passed with total_pages === 1', () => {
    const newProps = Object.assign({}, props, { pagination: toImmutable({ total_pages: 1 }) });
    tree.reRender(<List {...newProps} />);
    expect(tree.subTree('ListFrameContents').subTree('PaginationBoxView')).toBeFalsy();
  });
});
