import React, {Component, PropTypes} from 'react';
import { ContentCard } from './ContentCard';
import { ContentCommentCard } from './ContentCommentCard';
import { contentSelector, articlesSelector, newsSelector, downloadsSelector,
  articlesCommentsSelector, newsCommentsSelector, downloadsCommentsSelector }
  from '../../../../Selectors/list';
import { peopleSelector, articlesRecordsSelector, newsRecordsSelector, downloadsRecordsSelector }
  from '../../../../Selectors/recordStores';
import { toggleSelectedAction } from '../../../../Actions/publishMassActions';


import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: peopleSelector(state),
    linkedArticles: articlesRecordsSelector(state),
    linkedNews: newsRecordsSelector(state),
    linkedDownloads: downloadsRecordsSelector(state),
    content: contentSelector(state),
    articles: articlesSelector(state),
    news: newsSelector(state),
    downloads: downloadsSelector(state),
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
    people: PropTypes.object.isRequired,
    linkedArticles: PropTypes.object,
    linkedNews: PropTypes.object,
    linkedDownloads: PropTypes.object,
    articles: PropTypes.object,
    news: PropTypes.object,
    downloads: PropTypes.object,
    article_comments: PropTypes.object,
    news_comments: PropTypes.object,
    download_comments: PropTypes.object,
    selected: PropTypes.object.isRequired
  };

  toggleSelected(id) {
    //this.props.dispatch(toggleSelectedAction(id));
  }
/*

  toggleSelected(id) {
    this.props.dispatch(toggleSelectedAction(id));
  }
*/

  renderCommentCard(elements) {
    const { content, people, selected, linkedArticles, linkedNews, linkedDownloads } = this.props;
    const getParent = (element) => {
      switch (content) {
        case 'article_comments':
          console.log(linkedArticles.get(element.article));
          return linkedArticles.get(element.article);
        case 'download_comments':
          return linkedDownloads.get(element.download);
        case 'news_comments':
          return linkedNews.get(element.news);
        default:
      }
    };

    return (
      elements.map((element, index) =>
          <ContentCommentCard key={index}
                              element={element}
                              parent={getParent(element)}
                              toggleSelected={this.toggleSelected.bind(this)}
                              selected={selected.includes(element.id)}
                              author={people.get(element.person)}/>
      ));
  }

  render() {
    const { content, people, dispatch, selected } = this.props;
    const elements = this.props[content];

    return (
      <div>
        {['articles', 'news', 'downloads'].indexOf(content) > -1 &&
        elements.map((element, index) =>
            <ContentCard key={index}
                         element={element}
                         toggleSelected={this.toggleSelected.bind(this)}
                         selected={selected.includes(element.id)}
                         author={people.get(element.person)}
                         lastRevisionAuthor={people.get(element.last_author_id)}/>
        )}
        {['article_comments', 'news_comments', 'download_comments'].indexOf(content) > -1
        && this.renderCommentCard(elements)}
      </div>
    );
  }
}