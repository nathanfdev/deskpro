import React, {Component, PropTypes} from 'react';
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
export class ListContainer extends Component {


  static propTypes = {
    currentGroup: PropTypes.object.isRequired,
    currentViewMode: PropTypes.object.isRequired,
    comments: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired,
    people: PropTypes.array.isRequired,
    feedback: PropTypes.array.isRequired,
    feedbackTypes: PropTypes.array.isRequired,
    feedbackLabels: PropTypes.array.isRequired,
    feedbackComments: PropTypes.array.isRequired,
    feedbackStatuses: PropTypes.array.isRequired,
    feedbackFromStore: PropTypes.array.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.array.isRequired
  };

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