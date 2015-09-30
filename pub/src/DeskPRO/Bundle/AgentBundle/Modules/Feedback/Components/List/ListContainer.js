import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewDataSelector, peopleSelector, feedbackTypesSelector, feedbackLabelsSelector, feedbackCommentsSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    massAction: state.Feedback.list.get('massAction'),
    feedback: state.Feedback.list.get('feedback'),
    comments: state.Feedback.list.get('comments'),
    currentContent: state.Feedback.list.get('currentContent'),
    currentViewMode: viewDataSelector(state),
    people: peopleSelector(state),
    feedbackTypes: feedbackTypesSelector(state),
    feedbackLabels: feedbackLabelsSelector(state),
    feedbackComments: feedbackCommentsSelector(state)
  });
})

export class ListContainer extends React.Component {
  render() {
    const {massAction, feedback, comments, currentContent, currentViewMode, people, feedbackTypes, feedbackLabels, feedbackComments } = this.props;

    return (
      <List
        massAction={massAction}
        feedback={feedback}
        comments={comments}
        currentContent={currentContent}
        currentViewMode={currentViewMode}
        people={people}
        feedbackTypes={feedbackTypes}
        feedbackLabels={feedbackLabels}
        feedbackComments={feedbackComments}
        />
    );
  }
}