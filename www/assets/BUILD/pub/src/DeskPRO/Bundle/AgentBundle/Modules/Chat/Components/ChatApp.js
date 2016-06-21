import React from 'react';
import { connect } from 'react-redux';
import { AppPane, NavPaneContainer, ListPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class ChatApp extends React.Component {
  render() {
    return (
      <AppPane>
        <NavPaneContainer><NavContainer /></NavPaneContainer>
        <ListPane><ListContainer /></ListPane>
      </AppPane>
    );
  }
}
