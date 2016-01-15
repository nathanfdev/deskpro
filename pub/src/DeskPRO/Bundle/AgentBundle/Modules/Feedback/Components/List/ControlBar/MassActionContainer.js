import React, {Component, PropTypes} from 'react';
import { MassActionBarContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { toggleMassAction } from '../../../../Application/Actions/massActions';
import { deleteFeedback, approveFeedback } from '../../../Actions/FeedbackListActions';
import { massActionsSelector, currentListParamsSelector, isCommentsSelector } from '../../../Selectors/list';
import { deleteComment, approveComment }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

import { connect } from 'react-redux';
@connect(state => ({
  currentListParams: currentListParamsSelector(state),
  isComments: isCommentsSelector(state),
  actions: massActionsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentListParams: PropTypes.object.isRequired,
    isComments: PropTypes.bool,
    actions: PropTypes.array.isRequired
  };

  choiceActions() {
    const {currentListParams, actions, isComments, dispatch} = this.props;
    if (currentListParams.get('navItem') && currentListParams.get('navItem').get('awaiting_validation')) {
      const ids = [];
      const deleteAction = () => {
        if (isComments) {
          dispatch(deleteComment(ids));
        } else {
          dispatch(deleteFeedback(ids));
        }
        return dispatch(toggleMassAction());
      };
      const approveAction = () => {
        if (isComments) {
          dispatch(approveComment(ids));
        } else {
          dispatch(approveFeedback(ids));
        }
        return dispatch(toggleMassAction());
      };
      return [
        { label: 'Approve', type: 'button', onClick: approveAction },
        { label: 'Delete', type: 'button', onClick: deleteAction }
      ];
    }
    return actions;
  }

  render() {
    const { isComments } = this.props;

    const config = {
      actions: this.choiceActions(),
      jobType: 'publish_mass',
      content: isComments ? 'feedback_comments' : 'feedback'
    };


    return (
      <MassActionBarContainer {...config} />
    );
  }
}