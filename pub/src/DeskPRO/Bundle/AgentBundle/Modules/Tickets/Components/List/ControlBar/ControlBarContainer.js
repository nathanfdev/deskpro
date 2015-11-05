import React, { Component, PropTypes } from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { MassActionCheckboxContainer } from './MassActionCheckboxContainer';

export class ControlBarContainer extends Component {
  render() {
    return (
      <ListFrameMenu>
        <MassActionCheckboxContainer />
      </ListFrameMenu>
    );
  }
}