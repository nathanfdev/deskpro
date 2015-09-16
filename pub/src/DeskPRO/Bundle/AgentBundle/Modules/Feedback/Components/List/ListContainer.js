import React from 'react';
import { List } from './List';
import { connect } from 'redux/react';

@connect(state => ({
  elements: state.FeedbackList.feedback,
  viewModeOptions: state.FeedbackList.viewModeOptions
}))
export class ListContainer extends React.Component {
  render() {

    return (
      <List {...this.props} />
    );
  }
}