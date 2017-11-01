import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ContentCard } from './ContentCard';
import { ContentCommentCard } from './ContentCommentCard';
import { ArticlePendingCreateCard } from './ArticlePendingCreateCard';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { contentSelector, elementsSelector } from '../../../../Selectors/list';
import {
  articlesSelector, newsSelector, downloadsSelector,
  articlesCommentsSelector, newsCommentsSelector, downloadsCommentsSelector, articlePendingCreatesSelector
}
  from '../../../../Selectors/recordStores';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  content:                 contentSelector(state),
  elements:                elementsSelector(state),
  people:                  collectionSelectorFactory('Person', 'publish')(state),
  articles:                articlesSelector(state),
  news:                    newsSelector(state),
  downloads:               downloadsSelector(state),
  article_pending_creates: articlePendingCreatesSelector(state),
  article_comments:        articlesCommentsSelector(state),
  news_comments:           newsCommentsSelector(state),
  download_comments:       downloadsCommentsSelector(state),
  selected:                selectedSelector(state)
}))
export class CardsContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    content:           PropTypes.string.isRequired,
    elements:          PropTypes.array.isRequired,
    people:            PropTypes.object.isRequired,
    articles:          PropTypes.object,
    news:              PropTypes.object,
    downloads:         PropTypes.object,
    article_comments:  PropTypes.object,
    news_comments:     PropTypes.object,
    download_comments: PropTypes.object,
    selected:          PropTypes.object.isRequired
  };

  toggleSelected = (id, e) => {
    e.stopPropagation();
    const { dispatch } = this.props;
    dispatch(toggleSelectedAction(id));
  };

  renderContentCard = id => {
    const { content, people, selected } = this.props;
    const element = this.props[content].get(id);
    const toggle    = this.toggleSelected.bind(this, id);

    return (
      <ContentCard
        key={id}
        element={element}
        toggleSelected={toggle}
        selected={selected.indexOf(id) > -1}
        author={people.get(element.get('person'))}
        lastRevisionAuthor={people.get(element.get('last_author_id'))}
      />
    );
  };

  renderCommentCard = id => {
    const { content, people, selected, articles, news, downloads } = this.props;
    const element   = this.props[content].get(id);
    const toggle    = this.toggleSelected.bind(this, id);
    const getParent = () => {
      switch (content) {
        case 'article_comments':
          return articles.get(element.get('article'));
        case 'download_comments':
          return downloads.get(element.get('download'));
        case 'news_comments':
          return news.get(element.get('news'));
        default:
      }
      return null;
    };

    return (
      <ContentCommentCard
        key={id}
        element={element}
        parent={getParent()}
        toggleSelected={toggle}
        selected={selected.includes(element.get('id'))}
        author={people.get(element.get('person'))}
      />
    );
  };

  renderArticlePendingCreate = id => {
    const { content, people, selected } = this.props;
    const element = this.props[content].get(id);
    const toggle    = this.toggleSelected.bind(this, id);

    return (
      <ArticlePendingCreateCard
        key={id}
        element={element}
        toggleSelected={toggle}
        selected={selected.includes(element.get('id'))}
        author={people.get(element.get('person'))}
        assigned={people.get(element.get('assigned_person'))}
      />
    );
  };

  render = () => {
    const { content, elements } = this.props;

    return (
      <div>
        {['articles', 'news', 'downloads'].indexOf(content) > -1 && elements &&
        elements.map(id => this.renderContentCard(id))}

        {content === 'article_pending_creates' && elements &&
        elements.map(id => this.renderArticlePendingCreate(id))}

        {['article_comments', 'news_comments', 'download_comments'].indexOf(content) > -1 && elements &&
        elements.map(id => this.renderCommentCard(id))}
      </div>
    );
  }
}
