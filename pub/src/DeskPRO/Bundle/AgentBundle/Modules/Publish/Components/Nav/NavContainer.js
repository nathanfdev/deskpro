import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import * as actions from '../../Actions/publishNavActions';
import * as listActions from '../../Actions/publishListActions';
import { Nav } from './Nav';

@connect(state => {
  return {
    loaded: state.Publish.nav.getIn(['async', 'done']),
    lists: state.Publish.nav.get('lists'),
    grouping: state.Publish.nav.get('grouping'),
    groups: state.Publish.nav.get('groups'),
    dpWindow: state.Application.dpWindow
  };
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    lists: PropTypes.object.isRequired,
    groups: PropTypes.object.isRequired,
    grouping: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(actions.initialLoad());
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

  toggleGroupingVisibility(listName) {
    return (event) => {
      event.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    };
  }

  render() {
    const { dispatch, lists, dpWindow, loaded, groups } = this.props;

    // select lists labels depending on their grouping
    const labels = {};
    ['articles', 'news', 'downloads'].forEach(list => {
      switch (lists.get(list).grouped_by) {
        case 'category':
          labels[list] = groups.get('categories').get(list);
          break;
        case 'author':
          labels[list] = groups.get('authors');
          break;
        case 'period_created':
        case 'period_updated':
          labels[list] = DatePeriods.all;
          break;
        default:
      }
    });
    labels.commentsToValidate = DatePeriods.all;


    const onClick = {
      articles: (group) => {
        dispatch(listActions.load('articles', lists.get('articles').get('grouped_by'), group));
      },
      news: (group) => {
        dispatch(listActions.load('news', lists.get('news').get('grouped_by'), group));
      },
      downloads: (group) => {
        dispatch(listActions.load('downloads', lists.get('downloads').get('grouped_by'), group));
      },
      draftArticles: () => {
        dispatch(listActions.loadDraftArticles(lists.get('todo').get('articles').get('mine')));
      },
      pendingArticles: () => {
        dispatch(listActions.loadPendingArticles(lists.get('todo').get('articles').get('mine')));
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
           labels={labels}
           grouping={this.props.grouping}
           onGroupingChange={this.onGroupingChange.bind(this)}
           toggleGroupingVisibility={this.toggleGroupingVisibility.bind(this)}
           setMine={this.setMine.bind(this)}
           onClick={onClick}
           dispatch={dispatch.bind(this)}
           dpWindow={dpWindow}/>
    );
  }

}
