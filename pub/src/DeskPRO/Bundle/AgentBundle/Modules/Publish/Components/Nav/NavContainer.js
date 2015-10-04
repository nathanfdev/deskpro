import React from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import * as actions from '../../Actions/publishNavActions'
import * as listActions from '../../Actions/publishListActions'
import { Nav } from './Nav';

@connect(state => {
  // select lists labels depending on their grouping
  const labels = {};
  ['articles', 'news', 'downloads'].forEach(list => {
    switch (state.PublishNav.lists[list].grouped_by) {
      case 'category':
        labels[list] = state.PublishNav.groups.categories[list];
        break;
      case 'author':
        labels[list] = state.PublishNav.groups.authors;
        break;
      case 'period_created':
      case 'period_updated':
        labels[list] = DatePeriods.all;
        break;
    }
  });

  labels.commentsToValidate = DatePeriods.all;

  return {labels, lists: state.PublishNav.lists, grouping: state.PublishNav.grouping};
})
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    const { dispatch, lists } = this.props;

    dispatch(actions.loadCounts('articles', lists.articles.grouped_by));
    dispatch(actions.loadCounts('news', lists.news.grouped_by));
    dispatch(actions.loadCounts('downloads', lists.downloads.grouped_by));
    dispatch(actions.loadCategories());
    dispatch(actions.loadDraftsCount(lists.todo.articles.mine));
    dispatch(actions.loadPendingCount(lists.todo.articles.mine));
    dispatch(actions.loadCommentsToValidateCounts());
    dispatch(actions.loadCommentsToReviewCount());
  }

  render() {
    const onGroupingChange = (listName) => this.onGroupingChange(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);
    const setMine = (isMine) => this.setMine(isMine).bind(this);

    const { dispatch, lists, dpWindow } = this.props;
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
      <Nav
        lists={this.props.lists}
        labels={this.props.labels}
        grouping={this.props.grouping}
        onGroupingChange={onGroupingChange}
        toggleGroupingVisibility={toggleGroupingVisibility}
        setMine={setMine}
        onClick={onClick}
        dispatch={dispatch.bind(this)}
        dpWindow={dpWindow}
      />
    );
  }

  toggleGroupingVisibility(listName) {
    return function(e) {
      e.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    }
  }

  onGroupingChange(listName) {
    return function(e) {
      const options = e.target.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          this.props.dispatch(actions.changeListGrouping(listName, options[i].value));
        }
      }
    }
  }

  setMine(isMine) {
    return function(e) {
      e.preventDefault();
      this.props.dispatch(actions.setMine(isMine));
    }
  }
}
