import React from 'react';
import { connect } from 'react-redux';
import { AppPane, NavPane, ListPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class CrmApp extends React.Component {
  render() {
    return (
      <AppPane>
        <NavPane><NavContainer/></NavPane>
        <ListPane><ListContainer/></ListPane>
      </AppPane>
    );
  }
}
