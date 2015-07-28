import React from "react";

import AppContainer from "DeskPRO/Component/AppContainer";

import TicketsSidebarHoverFrame from "./TicketsSidebarHoverFrame";
import TicketsNavFrame from "./TicketsNavFrame";
import TicketsListFrame from "./TicketsListFrame";

export default class TicketsApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tickets" {...this.props}>
        <TicketsSidebarHoverFrame />
        <TicketsNavFrame {...this.props} />
        <TicketsListFrame />
      </AppContainer>
    );
  }
}
