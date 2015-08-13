import React from "react";
import { connect } from 'react-redux';
import AppContainer from "DeskPRO/Component/AppContainer";

import TicketsSidebarHoverFrame from "./TicketsSidebarHoverFrame";
import TicketsNavFrame from "./TicketsNavFrame";
import TicketsListFrame from "./TicketsListFrame";

@connect(state => ({
  user: state.Tasks.user,
  dp_window: state.Application.dp_window
}))
export default class TicketsApp extends React.Component {
  render() {
    return (<div />);
    return (
      <AppContainer thisAppId="tickets" {...this.props}>
        <TicketsSidebarHoverFrame />
        <TicketsNavFrame {...this.props} />
        <TicketsListFrame />
      </AppContainer>
    );
  }
}
