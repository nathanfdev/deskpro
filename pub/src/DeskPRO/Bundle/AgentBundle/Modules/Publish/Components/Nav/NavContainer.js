import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/publishNavActions';
import * as listActions from '../../Actions/publishListActions';
import { Nav } from './Nav';

@connect(state => {
  return {
    loaded: state.Publish.nav.getIn(['async', 'done']),
    articles: state.Publish.nav.get('articles'),
    news: state.Publish.nav.get('news'),
    downloads: state.Publish.nav.get('downloads'),
    todo: state.Publish.nav.get('todo'),
    grouping: state.Publish.nav.get('grouping'),
    dpWindow: state.Application.dpWindow
  };
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    articles: PropTypes.object.isRequired,
    news: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired,
    todo: PropTypes.object.isRequired,
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
    this.props.dispatch(actions.setMine(isMine));
  }

  toggleGroupingVisibility(listName) {
    return (event) => {
      event.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    };
  }

  render() {
    const { dispatch, articles, news, downloads, todo, dpWindow, loaded } = this.props;

    const onClick = {
      articles: (group) => {
        dispatch(listActions.load('articles', articles.get('grouped_by'), group));
      },
      news: (group) => {
        dispatch(listActions.load('news', news.get('grouped_by'), group));
      },
      downloads: (group) => {
        dispatch(listActions.load('downloads', downloads.get('grouped_by'), group));
      },
      draftArticles: () => {
        dispatch(listActions.loadDraftArticles(todo.get('articles').get('mine')));
      },
      pendingArticles: () => {
        dispatch(listActions.loadPendingArticles(todo.get('articles').get('mine')));
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
           articles={articles}
           news={news}
           downloads={downloads}
           todo={todo}
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
