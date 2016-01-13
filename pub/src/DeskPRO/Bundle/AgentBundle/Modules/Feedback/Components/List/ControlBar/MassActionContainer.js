import React, {Component, PropTypes} from 'react';
import { MassActionBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBar';
import { selectedSelector } from '../../../../Application/Selectors/massActions';
import { toggleMassAction } from '../../../../Application/Actions/massActions';
import { deleteFeedback, approveFeedback } from '../../../Actions/FeedbackListActions';
import { massAction, setMassActionsParams, resetAllMassActionsParams, resetMassActionsParam }
  from '../../../Actions/FeedbackMassActions';
import { massActionsSelector, massActionsParamsSelector, currentListParamsSelector } from '../../../Selectors/list';
import { deleteComment, approveComment }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

import { connect } from 'react-redux';
@connect(state => ({
  selected: selectedSelector(state),
  currentListParams: currentListParamsSelector(state),
  actions: massActionsSelector(state),
  currentMassActionsParams: massActionsParamsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selected: PropTypes.object.isRequired,
    currentListParams: PropTypes.object.isRequired,
    actions: PropTypes.array.isRequired,
    currentMassActionsParams: PropTypes.object
  };

  massActionsSubmit() {
    const {dispatch, selected, currentMassActionsParams} = this.props;
    const ids = selected.toArray();
    const params = currentMassActionsParams.toJS();
    dispatch(massAction({ ids: ids, actions: params }));
  }

  massActionsCancel() {
    const {dispatch} = this.props;
    dispatch(resetAllMassActionsParams());
  }

  choiceActions() {
    const {currentListParams, actions, selected, dispatch} = this.props;
    if (currentListParams.get('navItem') && currentListParams.get('navItem').get('awaiting_validation')) {
      const ids = selected.toArray();
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
      selected: this.props.selected,
      actions: this.choiceActions(),
      setParams: setMassActionsParams,
      submitAction: this.massActionsSubmit.bind(this),
      cancelAction: this.massActionsCancel.bind(this),
      resetSingleAction: resetMassActionsParam,
      currentParams: this.props.currentMassActionsParams
    };


    return (
      <MassActionBar {...config} />
    );
  }
}