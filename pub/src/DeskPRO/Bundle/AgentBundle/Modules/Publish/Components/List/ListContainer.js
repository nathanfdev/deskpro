import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';

@connect(state => {
  return ({
    elements: state.Publish.list.get(state.Publish.list.get('content')),
    content: state.Publish.list.get('content'),
    view: state.Publish.list.get('view'),
  });
})
export class ListContainer extends React.Component {

  render() {
    const {content, elements, view} = this.props;
    console.log(elements);
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
        throw `Unknown list ${this.props.content}`;
    }
  }

  toggleView(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleView());
  }
}
