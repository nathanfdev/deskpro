import React from "react";
import { bindActionCreators } from 'redux';
import { connect } from 'redux/react';

import * as AppActions from "../Actions/AppActions";
import * as TaskActions from "../../Tasks/Actions/TaskListActions";

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import TabFrame from "./TabFrame";

import TicketsApp from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/TicketsApp";
import TasksApp from "DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/TasksApp";

import TicketsSidebarHoverFrame from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/TicketsSidebarHoverFrame";

@connect(state => ({
  user: state.user,
  dp_window: state.dp_window
}))
export default class DpApp extends React.Component {

  render() {
    const { user, dp_window, dispatch } = this.props;
    const actions = bindActionCreators(AppActions, dispatch);

    return (
      <div className="dp-window">
        <Header user={user} />
        <AppSwitcher switchApp={actions.setActiveApp} activeAppId={dp_window.activeAppId} />

        <TicketsApp {...this.props} />
        <TasksApp {...this.props} />
      
        <TabFrame />
      </div>
    );
  }
}
