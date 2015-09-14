import React from 'react';
import { List } from './List';
import { connect } from 'redux/react';

@connect(state => ({
  elements: state.FeedbackList.feedback,
  viewMode: state.FeedbackList.viewMode
}))
export class ListContainer extends React.Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}