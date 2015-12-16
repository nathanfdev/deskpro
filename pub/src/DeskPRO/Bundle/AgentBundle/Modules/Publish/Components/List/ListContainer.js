import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';

@connect(state => {
  return ({
    elements: state.Publish.list.get(state.Publish.list.get('content')),
    content: state.Publish.list.get('content'),
    view: state.Publish.list.get('view')
  });
})
export class ListContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    content: PropTypes.string.isRequired,
    elements: PropTypes.object.isRequired,
    view: PropTypes.string.isRequired
  };

  toggleView(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleView());
  }

  render() {
    const {content, elements, view} = this.props;
    switch (content) {
      case 'articles':
      case 'news':
      case 'downloads':
      case 'draftArticles':
      case 'pendingArticles':
      case 'commentsToValidate':
      case 'commentsToReview':
        return (
          <List
            elements={elements}
            view={view}
            toggleView={this.toggleView.bind(this)}
            />
        );

      default:
        throw new Error(`Unknown list ${this.props.content}`);
    }
  }
}
