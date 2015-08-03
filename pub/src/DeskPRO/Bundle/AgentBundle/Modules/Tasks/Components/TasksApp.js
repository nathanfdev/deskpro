import React from "react";
import { connect } from 'redux/react';
import AppContainer from "DeskPRO/Component/AppContainer";

//import TasksSidebarHoverFrame from "./TasksSidebarHoverFrame";
import TasksNavFrame from "./TasksNavFrame";
import TasksListFrame from "./TasksListFrame";

@connect(state => ({
  user: state.user,
  dp_window: state.dp_window
}))
export default class TasksApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tasks" {...this.props}>
        <TasksNavFrame {...this.props} />
        <TasksListFrame />
      </AppContainer>
    );
  }
}
