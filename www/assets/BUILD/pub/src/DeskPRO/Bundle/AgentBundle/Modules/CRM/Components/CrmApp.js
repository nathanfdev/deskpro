import React from 'react';
import { AppPane, NavPaneContainer, ListPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

export class CrmApp extends React.Component {
  render = () =>
    <AppPane>
      <NavPaneContainer><NavContainer /></NavPaneContainer>
      <ListPane><ListContainer /></ListPane>
    </AppPane>
}
