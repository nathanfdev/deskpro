import React, {Component, PropTypes} from 'react';
import { List } from './List';
import { feedbackTypesSelector, feedbackLabelsSelector, feedbackCommentsSelector,
         feedbackStatusesSelector, feedbackSelector, isCommentsSelector, currentViewModeSelector }
  from '../../Selectors/list';
import { toggleSelectedAction } from '../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

@connect(state => {
  return ({
    elements: state.Feedback.list.get('elements'),
    isComments: isCommentsSelector(state),
    selected: state.Feedback.list.get('selected'),
    currentViewMode: currentViewModeSelector(state),
    feedbackTypes: feedbackTypesSelector(state),
    feedbackLabels: feedbackLabelsSelector(state),
    feedbackComments: feedbackCommentsSelector(state),
    feedbackFromStore: feedbackSelector(state),
    feedbackStatuses: feedbackStatusesSelector(state)
  });
})
export class ListContainer extends Component {

  static propTypes = {
    elements: PropTypes.object.isRequired,
    isComments: PropTypes.bool,
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
    selected: PropTypes.array.isRequired
  };

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));

    return (
      <List {...this.props} toggleSelected={toggleSelected} />
    );
  }
}
