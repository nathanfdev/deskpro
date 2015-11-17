import React, {Component, PropTypes} from 'react';
import { List } from './List';
import { feedbackTypesSelector, feedbackLabelsSelector, feedbackCommentsSelector,
         feedbackStatusesSelector, feedbackSelector, isCommentsSelector, currentViewModeSelector }
  from '../../Selectors/list';
import { toggleSelectedAction } from '../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

@connect(state => {
  return ({
    isComments: isCommentsSelector(state),
    selected: state.Feedback.list.get('selected'),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {

  static propTypes = {
    elements: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    isComments: PropTypes.bool,
    currentViewMode: PropTypes.string.isRequired
  };

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));

    return (
      <List {...this.props} toggleSelected={toggleSelected} />
    );
  }
}
