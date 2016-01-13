import React, {Component, PropTypes} from 'react';
import { MassActionBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBar';
import { massActionsSelector } from '../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => ({
  actions: massActionsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    actions: PropTypes.array.isRequired
  };

  render() {
    const config = {
      actions: this.props.actions
    };


    return (
      <MassActionBar {...config} />
    );
  }
}