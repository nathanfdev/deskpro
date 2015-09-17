import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';

@connect(state => state.PublishList)
export class ListContainer extends React.Component {

  render() {
    switch (this.props.content) {
      case 'articles':
      case 'news':
      case 'downloads':
      case 'draftArticles':
      case 'pendingArticles':
      case 'commentsToValidate':
      case 'commentsToReview':
        return (
          <List
            elements={this.props[this.props.content]}
            view={this.props.view}
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
