import React, {Component, PropTypes} from 'react';
import { MassActionBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBar';
// import { deleteFeedback, approveFeedback } from '../../../Actions/FeedbackListActions';
// import { toggleMassAction, massAction, setMassActionsParams, resetAllMassActionsParams, resetMassActionsParam }
//  from '../../../Actions/FeedbackMassActions';
 import { massActionsSelector, massActionsParamsSelector, currentListParamsSelector } from '../../../Selectors/list';
// import { deleteComment, approveComment }
//  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

import { connect } from 'react-redux';
@connect(state => ({
  selected: state.Feedback.list.get('selected'),
  actions: massActionsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    selected: PropTypes.object.isRequired,
    actions: PropTypes.array.isRequired
  };

  render() {
    const config = {
      selected: this.props.selected,
      actions: this.props.actions
    };


    return (
      <MassActionBar {...config} />
    );
  }
}