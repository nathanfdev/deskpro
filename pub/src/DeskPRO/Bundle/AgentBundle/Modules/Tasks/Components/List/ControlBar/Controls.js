import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { MassActionCheckboxContainer } from './MassActionCheckboxContainer';
import { OrderByContainer } from './OrderByContainer';
import { FilterByContainer } from './FilterByContainer';
import { ViewModeSwitcherContainer } from './ViewModeSwitcherContainer';

export class Controls extends React.Component {

  render() {
    return (
      <ListFrameMenu>
        <MassActionCheckboxContainer />
        <OrderByContainer />
        <li>
          <hr/>
        </li>
        <FilterByContainer />
        <li>
          <hr/>
        </li>
        <ViewModeSwitcherContainer />
      </ListFrameMenu>
    );
  }
}
