import React, {Component, PropTypes} from 'react';
import { List } from './List';
import { isCommentsSelector, currentViewModeSelector } from '../../Selectors/list';
import { toggleSelectedAction } from '../../Actions/FeedbackMassActions';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    isComments: isCommentsSelector(state),
    content: state.Feedback.list.get('content'),
    selected: state.Feedback.list.get('selected'),
    pagination: state.Feedback.list.get('pagination'),
    loaded: state.Feedback.list.getIn(['async', 'done']),
    currentApp: state.Application.dpWindow.get('activeAppId'),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentApp: PropTypes.string.isRequired,
    isComments: PropTypes.bool,
    loaded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));

    return (
      <List {...this.props} toggleSelected={toggleSelected}/>
    );
  }
}
