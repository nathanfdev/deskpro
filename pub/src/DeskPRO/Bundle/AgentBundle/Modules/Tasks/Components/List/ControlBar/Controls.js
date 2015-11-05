import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { OrderBy, FilterBy, ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { MassActionCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar';

export class Controls extends React.Component {

  render() {
    return (
      <ListFrameMenu>
        <MassActionCheckbox />
        <OrderBy />
        <li>
          <hr/>
        </li>
        <FilterBy />
        <li>
          <hr/>
        </li>
        <ViewModeSwitcher />
      </ListFrameMenu>
    );
  }
}
