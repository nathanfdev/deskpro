import React, {Component, PropTypes} from 'react';
import { MassActionBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBar';
import { toggleMassAction, massAction, setMassActionsParams }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { massActionsSelector } from '../../../Selectors/list';


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
      checkbox: {
        count: this.props.selected.size,
        action: toggleMassAction
      },
      selected: this.props.selected,
      actions: this.props.actions,
      setParams: setMassActionsParams,
      action: massAction
    };


    return (
      <MassActionBar {...config} />
    );
  }
}