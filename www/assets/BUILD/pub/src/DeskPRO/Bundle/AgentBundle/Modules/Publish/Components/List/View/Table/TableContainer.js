import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import {
  elementsSelector, contentSelector, currentListOrderBySelector, currentListOrderDirSelector
}
  from '../../../../Selectors/list';
import {
  articlesSelector, newsSelector, downloadsSelector,
  articlesCommentsSelector, newsCommentsSelector, downloadsCommentsSelector, articlePendingCreatesSelector
}
  from '../../../../Selectors/recordStores';
import { applyParams } from '../../../../Actions/publishListActions';
import { ContentTable } from './ContentTable';
import { CommentTable } from './CommentTable';
import { APCTable } from './APCTable';

@connect(state => ({
  elements:                elementsSelector(state),
  people:                  collectionSelectorFactory('Person', 'publish')(state),
  content:                 contentSelector(state),
  articles:                articlesSelector(state),
  news:                    newsSelector(state),
  downloads:               downloadsSelector(state),
  article_pending_creates: articlePendingCreatesSelector(state),
  article_comments:        articlesCommentsSelector(state),
  news_comments:           newsCommentsSelector(state),
  download_comments:       downloadsCommentsSelector(state),
  orderBy:                 currentListOrderBySelector(state),
  orderDir:                currentListOrderDirSelector(state)
}))

export class TableContainer extends Component {
  static propTypes = {
    dispatch:  PropTypes.func.isRequired,
    elements:  PropTypes.array.isRequired,
    content:   PropTypes.string.isRequired,
    articles:  PropTypes.object,
    downloads: PropTypes.object,
    news:      PropTypes.object,
    people:    PropTypes.object.isRequired,
    orderBy:   PropTypes.string.isRequired,
    orderDir:  PropTypes.string.isRequired
  };

  sortTable = (param, dir) => {
    this.props.dispatch(applyParams({ order_by: param, order_dir: dir }));
  };

  renderContentTable = config => (<ContentTable {...config} />);

  renderCommentTable = config => {
    const getParents = () => {
      switch (this.props.content) {
        case 'article_comments':
          return this.props.articles;
        case 'download_comments':
          return this.props.downloads;
        case 'news_comments':
          return this.props.news;
        default:
      }
      return null;
    };

    return (
      <CommentTable {...config} parents={getParents()} />
    );
  };

  renderAPCTable = config => (<APCTable {...config} />);

  render = () => {
    const { content, elements, people, orderBy, orderDir } = this.props;
    const config = {
      content,
      people,
      orderBy,
      orderDir,

      ids:       elements,
      elements:  this.props[content],
      sortTable: this.sortTable
    };

    return (
      <div>
        {elements && ['articles', 'news', 'downloads'].indexOf(content) > -1
        && this.renderContentTable(config)}

        {elements && ['article_comments', 'news_comments', 'download_comments'].indexOf(content) > -1
        && this.renderCommentTable(config)}

        {elements && content === 'article_pending_creates'
        && this.renderAPCTable(config)}
      </div>
    );
  }
}
