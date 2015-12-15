import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import * as actions from '../../Actions/publishNavActions';
import * as listActions from '../../Actions/publishListActions';
import { Nav } from './Nav';

@connect(state => {
  // select lists labels depending on their grouping
  const labels = {};
  ['articles', 'news', 'downloads'].forEach(list => {
    switch (state.Publish.nav.get('lists').get(list).grouped_by) {
      case 'category':
        labels[list] = state.Publish.nav.get('groups').get('categories').get(list);
        break;
      case 'author':
        labels[list] = state.Publish.nav.get('groups').get('authors');
        break;
      case 'period_created':
      case 'period_updated':
        labels[list] = DatePeriods.all;
        break;
      default:
    }
  });

  labels.commentsToValidate = DatePeriods.all;

  return {
    labels,
    loaded: state.Publish.nav.getIn(['async', 'done']),
    lists: state.Publish.nav.get('lists'),
    grouping: state.Publish.nav.get('grouping'),
    dpWindow: state.Application.dpWindow
  };
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    lists: PropTypes.object.isRequired,
    grouping: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  componentDidMount() {
    const { dispatch, lists } = this.props;

    dispatch(actions.loadCounts('articles', lists.get('articles').get('grouped_by')));
    dispatch(actions.loadCounts('news', lists.get('news').get('grouped_by')));
    dispatch(actions.loadCounts('downloads', lists.get('downloads').get('grouped_by')));
    dispatch(actions.loadCategories());
    dispatch(actions.loadDraftsCount(lists.get('todo').get('articles').mine));
    dispatch(actions.loadPendingCount(lists.get('todo').get('articles').mine));
    dispatch(actions.loadCommentsToValidateCounts());
    dispatch(actions.loadCommentsToReviewCount());
  }

  render() {
    const { dispatch, lists, dpWindow, loaded } = this.props;
    const onClick = {
      articles: (group) => {
        dispatch(listActions.load('articles', lists.articles.grouped_by, group));
      },
      news: (group) => {
        dispatch(listActions.load('news', lists.news.grouped_by, group));
      },
      downloads: (group) => {
        dispatch(listActions.load('downloads', lists.downloads.grouped_by, group));
      },
      draftArticles: () => {
        dispatch(listActions.loadDraftArticles(lists.todo.articles.mine));
      },
      pendingArticles: () => {
        dispatch(listActions.loadPendingArticles(lists.todo.articles.mine));
      },
      commentsToValidate: (group) => {
        dispatch(listActions.loadCommentsToValidate('period_created', group));
      },
      allCommentsToValidate: () => {
        dispatch(listActions.loadCommentsToValidate());
      },
      commentsToReview: () => {
        dispatch(listActions.loadCommentsToReview());
      }
    };

    return (
      <Nav loaded={loaded}
           lists={this.props.lists}
           labels={this.props.labels}
           grouping={this.props.grouping}
           onGroupingChange={this.onGroupingChange.bind(this)}
           toggleGroupingVisibility={this.toggleGroupingVisibility.bind(this)}
           setMine={this.setMine.bind(this)}
           onClick={onClick}
           dispatch={dispatch.bind(this)}
           dpWindow={dpWindow}
        />
    );
  }

  toggleGroupingVisibility(listName) {
    return (event) => {
      event.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    };
  }

  onGroupingChange(listName) {
    return (event) => {
      const options = event.target.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          this.props.dispatch(actions.changeListGrouping(listName, options[i].value));
        }
      }
    };
  }

  setMine(isMine) {
    return (event) => {
      event.preventDefault();
      this.props.dispatch(actions.setMine(isMine));
    };
  }
}
