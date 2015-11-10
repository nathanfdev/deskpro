import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { OrderBy, FilterBy, ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class Controls extends React.Component {

  render() {
    return (
      <ListFrameMenu>
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
