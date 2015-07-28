import React from "react";

import AppContainer from "DeskPRO/Component/AppContainer";

//import TasksSidebarHoverFrame from "./TasksSidebarHoverFrame";
import TasksNavFrame from "./TasksNavFrame";
import TasksListFrame from "./TasksListFrame";

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
