import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { MassActionBarContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
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
      <MassActionBarContainer {...config} />
    );
  }
}
