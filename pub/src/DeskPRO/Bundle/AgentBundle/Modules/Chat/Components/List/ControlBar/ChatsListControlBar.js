import React from 'react';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { OrderByContainer } from '../../../Containers/List/ControlBar/OrderByContainer';
import { ViewSwitcherContainer } from '../../../Containers/List/ControlBar//ViewSwitcherContainer';

export class ChatsListControlBar extends React.Component {
  render() {
    return (
      <ControlBar>
        <OrderByContainer />
        <ViewSwitcherContainer />
      </ControlBar>
    );
  }
}
