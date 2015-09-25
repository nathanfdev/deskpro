import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewDataSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    feedback: state.Feedback.list.get('feedback'),
    comments: state.Feedback.list.get('comments'),
    currentContent: state.Feedback.list.get('currentContent'),
    currentViewMode: viewDataSelector(state)
  });
})

export class ListContainer extends React.Component {
  render() {
    const {feedback, comments, currentContent, currentViewMode} = this.props;
    return (
      <List
        feedback={feedback}
        comments={comments}
        currentContent={currentContent}
        currentViewMode={currentViewMode}
        />
    );
  }
}