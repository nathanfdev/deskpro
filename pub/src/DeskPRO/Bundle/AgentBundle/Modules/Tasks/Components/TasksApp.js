import React from 'react';
import { connect } from 'react-redux';
import { AppPane, NavPane, ListPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

export class TasksApp extends React.Component {
  render() {
    return (
      <AppPane>
        <NavPane><NavContainer/></NavPane>
        <ListPane><ListContainer/></ListPane>
      </AppPane>
    );
  }
}
