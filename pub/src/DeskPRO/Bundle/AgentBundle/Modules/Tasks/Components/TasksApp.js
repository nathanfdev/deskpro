import React from "react";
import { connect } from 'redux/react';
import AppContainer from "DeskPRO/Component/AppContainer";
import HTML5Backend from 'react-dnd/modules/backends/HTML5';
import { DragDropContext } from 'react-dnd';
import TaskCardDragLayer from './TaskCardDragLayer';

//import TasksSidebarHoverFrame from "./TasksSidebarHoverFrame";
import TasksNavFrame from "./TasksNavFrame";
import TasksListFrame from "./TasksListFrame";

@connect(state => ({
  user: state.user,
  dp_window: state.dp_window
}))
class TasksApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tasks" {...this.props}>
        <TasksNavFrame {...this.props} />
        <TasksListFrame {...this.props} />
        <TaskCardDragLayer />
      </AppContainer>
    );
  }
}

export default DragDropContext(HTML5Backend)(TasksApp);