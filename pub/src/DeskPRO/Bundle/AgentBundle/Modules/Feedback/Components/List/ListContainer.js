import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewDataSelector, peopleSelector, feedbackTypesSelector, feedbackLabelsSelector, feedbackCommentsSelector, feedbackStatusesSelector, feedbackSelector } from '../../Selectors/list';
import { toggleSelectedAction } from '../../Actions/FeedbackListActions';
import { groupDataSelector } from '../../Selectors/nav';

@connect(state => {
  return ({
    massAction: state.Feedback.list.get('massAction'),
    feedback: state.Feedback.list.get('feedback'),
    selected: state.Feedback.list.get('selected'),
    comments: state.Feedback.list.get('comments'),
    currentViewMode: viewDataSelector(state),
    people: peopleSelector(state),
    feedbackTypes: feedbackTypesSelector(state),
    feedbackLabels: feedbackLabelsSelector(state),
    feedbackComments: feedbackCommentsSelector(state),
    feedbackFromStore: feedbackSelector(state),
    feedbackStatuses: feedbackStatusesSelector(state),
    currentGroup: groupDataSelector(state)
  });
})
export class ListContainer extends React.Component {
  render() {
    const { currentGroup, massAction, feedback, selected, comments, currentViewMode, people, feedbackTypes,
            feedbackLabels, feedbackComments, feedbackStatuses, feedbackFromStore } = this.props;

    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));

    return (
      <List
        currentGroup={currentGroup}
        massAction={massAction}
        feedback={feedback}
        selected={selected}
        toggleSelected={toggleSelected}
        comments={comments}
        currentViewMode={currentViewMode}
        people={people}
        feedbackTypes={feedbackTypes}
        feedbackLabels={feedbackLabels}
        feedbackComments={feedbackComments}
        feedbackStatuses={feedbackStatuses}
        feedbackFromStore={feedbackFromStore}
        />
    );
  }
}