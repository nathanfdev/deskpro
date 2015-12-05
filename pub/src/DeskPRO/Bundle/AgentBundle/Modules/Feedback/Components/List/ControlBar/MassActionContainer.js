import React, {Component, PropTypes} from 'react';
import { MassActionBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBar';
import { toggleMassAction, massAction, setMassActionsParams, resetAllMassActionsParams, resetMassActionsParam }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { massActionsSelector, massActionsParamsSelector } from '../../../Selectors/list';


import { connect } from 'react-redux';
@connect(state => ({
  selected: state.Feedback.list.get('selected'),
  actions: massActionsSelector(state),
  currentMassActionsParams: massActionsParamsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selected: PropTypes.object.isRequired,
    actions: PropTypes.array.isRequired,
    currentMassActionsParams: PropTypes.object
  };

  massActionHandler(ids) {
    const {dispatch, currentMassActionsParams} = this.props;
    const params = currentMassActionsParams.toJS();
    dispatch(massAction({ ids: ids, actions: params }));
  }

  render() {
    const config = {
      checkbox: {
        count: this.props.selected.size,
        action: toggleMassAction
      },
      selected: this.props.selected,
      actions: this.props.actions,
      setParams: setMassActionsParams,
      action: this.massActionHandler.bind(this),
      resetAction: resetAllMassActionsParams,
      resetSingleAction: resetMassActionsParam,
      currentParams: this.props.currentMassActionsParams
    };


    return (
      <MassActionBar {...config} />
    );
  }
}