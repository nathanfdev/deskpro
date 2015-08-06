import React from "react";

import { connect } from 'redux/react';

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import TabFrame from "./TabFrame";

import TicketsApp from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/TicketsApp";
import TasksApp from "DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/TasksApp";

import { routingStarted } from "../Actions/AppActions";

@connect(state => ({
  ...state
}))
export default class DpApp extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, router } = this.props;
    dispatch(routingStarted(router));
  }

  render() {
    return (
      <div className="dp-window">
        <Header />
        <AppSwitcher />

        {this.props.children}

        <TabFrame {...this.props} />
      </div>
    );
  }
}
