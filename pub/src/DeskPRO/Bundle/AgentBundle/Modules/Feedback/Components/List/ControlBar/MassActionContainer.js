import React, {Component, PropTypes} from 'react';
import { MassActionBarContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { toggleMassAction } from '../../../../Application/Actions/massActions';
import { deleteFeedback, approveFeedback } from '../../../Actions/FeedbackListActions';
import { massAction }
  from '../../../Actions/FeedbackMassActions';
import { massActionsSelector, currentListParamsSelector } from '../../../Selectors/list';
import { deleteComment, approveComment }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

import { connect } from 'react-redux';
@connect(state => ({
  currentListParams: currentListParamsSelector(state),
  actions: massActionsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentListParams: PropTypes.object.isRequired,
    actions: PropTypes.array.isRequired,
    currentMassActionsParams: PropTypes.object
  };

  massActionsSubmit() {
    const {dispatch, currentMassActionsParams} = this.props;
    const ids = [];
    const params = currentMassActionsParams.toJS();
    dispatch(massAction({ ids: ids, actions: params }));
  }

  choiceActions() {
    const {currentListParams, actions, dispatch} = this.props;
    if (currentListParams.get('navItem') && currentListParams.get('navItem').get('awaiting_validation')) {
      const ids = [];
      const deleteAction = () => {
        if (currentListParams.get('isComments')) {
          dispatch(deleteComment(ids));
        } else {
          dispatch(deleteFeedback(ids));
        }
        return dispatch(toggleMassAction());
      };
      const approveAction = () => {
        if (currentListParams.get('isComments')) {
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
    const config = {
      actions: this.choiceActions(),
      submitAction: this.massActionsSubmit.bind(this)
    };


    return (
      <MassActionBarContainer {...config} />
    );
  }
}