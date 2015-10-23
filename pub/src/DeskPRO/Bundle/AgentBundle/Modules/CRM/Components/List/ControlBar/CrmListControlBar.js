import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { OrderByContainer } from './OrderByContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';

export class CrmListControlBar extends React.Component {
  render() {
    return (
      <ListFrameMenu>
          <OrderByContainer />
          <li>
            <hr/>
          </li>
          <ViewSwitcherContainer />
      </ListFrameMenu>
    );
  }
}