import React, {Component, PropTypes} from 'react';
import { ContentCard } from './ContentCard';
import { ContentCommentCard } from './ContentCommentCard';
import { ArticlePendingCreateCard } from './ArticlePendingCreateCard';
import { contentSelector, elementsSelector }
  from '../../../../Selectors/list';
import { peopleSelector, articlesSelector, newsSelector, downloadsSelector,
  articlesCommentsSelector, newsCommentsSelector, downloadsCommentsSelector, articlePendingCreatesSelector }
  from '../../../../Selectors/recordStores';
import { toggleSelectedAction } from '../../../../Actions/publishMassActions';


import { connect } from 'react-redux';
@connect(state => {
  return ({
    content: contentSelector(state),
    elements: elementsSelector(state),
    people: peopleSelector(state),
    articles: articlesSelector(state),
    news: newsSelector(state),
    downloads: downloadsSelector(state),
    article_pending_creates: articlePendingCreatesSelector(state),
    article_comments: articlesCommentsSelector(state),
    news_comments: newsCommentsSelector(state),
    download_comments: downloadsCommentsSelector(state),
    selected: state.Publish.list.get('selected')
  });
})

export class CardsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    content: PropTypes.string.isRequired,
    elements: PropTypes.array.isRequired,
    people: PropTypes.object.isRequired,
    articles: PropTypes.array,
    news: PropTypes.array,
    downloads: PropTypes.array,
    article_comments: PropTypes.object,
    news_comments: PropTypes.object,
    download_comments: PropTypes.object,
    selected: PropTypes.object.isRequired
  };

  toggleSelected(id) {
    //this.props.dispatch(toggleSelectedAction(id));
  }

  renderContentCard(id) {
    const { content, people, selected } = this.props;
    const element = this.props[content].get(id);

    return (
      <ContentCard key={id}
                   element={element}
                   toggleSelected={this.toggleSelected.bind(this)}
                   selected={selected.includes(id)}
                   author={people.get(element.get('person'))}
                   lastRevisionAuthor={people.get(element.get('last_author_id'))}/>
    );
  }

  renderCommentCard(id) {
    const { content, people, selected, articles, news, downloads } = this.props;
    const element = this.props[content].get(id);
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
    };

    return (
      <ContentCommentCard key={id}
                          element={element}
                          parent={getParent()}
                          toggleSelected={this.toggleSelected.bind(this)}
                          selected={selected.includes(element.get('id'))}
                          author={people.get(element.get('person'))}/>
    );
  }

  renderArticlePendingCreate(id) {
    const { content, people, selected } = this.props;
    const element = this.props[content].get(id);
    return (
      <ArticlePendingCreateCard key={id}
                                element={element}
                                toggleSelected={this.toggleSelected.bind(this)}
                                selected={selected.includes(element.get('id'))}
                                author={people.get(element.get('person'))}
                                assigned={people.get(element.get('assigned_person'))}/>
    );
  }

  render() {
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