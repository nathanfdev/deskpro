import React from 'react';
import { connect } from 'react-redux';
import { MassActionCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar';

@connect()
export class MassActionCheckboxContainer extends React.Component {

  render() {
    return (
      <MassActionCheckbox {...this.props} />
    );
  }
}
