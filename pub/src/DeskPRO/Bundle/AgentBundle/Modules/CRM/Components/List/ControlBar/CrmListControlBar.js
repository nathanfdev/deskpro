import React from 'react';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { OrderByContainer } from './OrderByContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';

export class CrmListControlBar extends React.Component {
  render() {
    return (
      <ControlBar>
        <OrderByContainer />
        <ViewSwitcherContainer />
      </ControlBar>
    );
  }
}