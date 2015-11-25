import React from 'react';
import { connect } from 'react-redux';
import { AppPane, NavPane, ListPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer } from './Nav/NavContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class ChatApp extends React.Component {
  render() {
    return (
      <AppPane>
        <NavPane><NavContainer/></NavPane>
        <ListPane><ListContainer/></ListPane>
      </AppPane>
    );
  }
}
