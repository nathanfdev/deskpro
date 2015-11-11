import React, {Component, PropTypes} from 'react';
import { List } from './List';
import { peopleSelector, emailsSelector, feedbackTypesSelector, feedbackLabelsSelector, feedbackCommentsSelector,
         feedbackStatusesSelector, feedbackSelector, isCommentsSelector, currentViewModeSelector }
  from '../../Selectors/list';
import { toggleSelectedAction } from '../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

@connect(state => {
  return ({
    feedback: state.Feedback.list.get('feedback'),
    massAction: state.Feedback.list.get('massAction'),
    selected: state.Feedback.list.get('selected'),
    comments: state.Feedback.list.get('comments'),
    currentViewMode: currentViewModeSelector(state),
    people: peopleSelector(state),
    emails: emailsSelector(state),
    feedbackTypes: feedbackTypesSelector(state),
    feedbackLabels: feedbackLabelsSelector(state),
    feedbackComments: feedbackCommentsSelector(state),
    feedbackFromStore: feedbackSelector(state),
    feedbackStatuses: feedbackStatusesSelector(state),
    isComments: isCommentsSelector(state)
  });
})
export class ListContainer extends Component {

  static propTypes = {
    feedback: PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    comments: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    people: PropTypes.object.isRequired,
    emails: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    feedbackLabels: PropTypes.object.isRequired,
    feedbackComments: PropTypes.object.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    feedbackFromStore: PropTypes.object.isRequired,
    massAction: PropTypes.bool.isRequired,
    selected: PropTypes.array.isRequired,
    isComments: PropTypes.bool
  };

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));

    return (
      <List {...this.props} toggleSelected={toggleSelected} />
    );
  }
}
