import React from 'react';
import { connect } from 'react-redux';
import HTML5Backend from 'react-dnd-html5-backend';
import { DragDropContext } from 'react-dnd';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
@DragDropContext(HTML5Backend)
export class TasksApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tasks" {...this.props}>
        <NavContainer />
        <ListContainer />
      </AppContainer>
    );
  }
}
