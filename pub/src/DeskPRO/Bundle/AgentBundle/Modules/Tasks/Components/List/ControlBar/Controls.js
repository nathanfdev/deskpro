import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { MassActionCheckboxContainer } from './MassAction/MassActionCheckboxContainer';
import { OrderByContainer } from './OrderBy/OrderByContainer';
import { FilterByContainer } from './FilterBy/FilterByContainer';
import { ViewModeSwitcherContainer } from './ViewMode/ViewModeSwitcherContainer';

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
