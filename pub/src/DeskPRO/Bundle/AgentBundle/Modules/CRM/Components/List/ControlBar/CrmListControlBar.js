import React from 'react';
import { ControlBar, ControlButtonsRow } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { OrderByContainer } from './OrderByContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';

export class CrmListControlBar extends React.Component {
  render() {
    return (
      <ControlBar>
        <ControlButtonsRow>
          <OrderByContainer />
          <li>
            <hr/>
          </li>
          <ViewSwitcherContainer />
        </ControlButtonsRow>
      </ControlBar>
    );
  }
}