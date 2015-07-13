import React from "react";
import { bindActionCreators } from 'redux';
import { connect } from 'redux/react';

import * as AppActions from "../Actions/AppActions";

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import NavFrame from "./NavFrame";
import ListFrame from "./ListFrame";
import TabFrame from "./TabFrame";
import AppFrameWrapper from "./AppFrameWrapper";

import TicketsNavFrame from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/TicketsNavFrame";
import TicketsListFrame from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/TicketsListFrame";

@connect(state => ({
  user: state.user,
  dp_window: state.dp_window
}))
export default class DpApp extends React.Component {
  render() {
    const { user, dp_window, dispatch } = this.props;
    const actions = bindActionCreators(AppActions, dispatch);

    return (<div className="dp-window">
        <Header user={user} actions={actions} />
        <AppSwitcher switchApp={actions.setActiveApp} activeAppId={dp_window.activeAppId} />
        <NavFrame activeAppId={dp_window.activeAppId}>
          <AppFrameWrapper appId="tickets" activeAppId={dp_window.activeAppId}><TicketsNavFrame /></AppFrameWrapper>
        </NavFrame>
        <div className="dp-content-outer-frame">
          <ListFrame>
            <AppFrameWrapper appId="tickets" activeAppId={dp_window.activeAppId}><TicketsListFrame /></AppFrameWrapper>
          </ListFrame>
          <TabFrame />
        </div>
      </div>);
  }
}
