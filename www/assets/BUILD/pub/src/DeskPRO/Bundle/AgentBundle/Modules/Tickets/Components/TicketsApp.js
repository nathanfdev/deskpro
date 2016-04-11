import React from 'react';
import { AppPane, NavPane, ListPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

export class TicketsApp extends React.Component {
  render = () =>
    <AppPane>
      <NavPane><NavContainer /></NavPane>
      <ListPane><ListContainer /></ListPane>
    </AppPane>;
}
