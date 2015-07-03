import React from "react";

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import NavFrame from "./NavFrame";
import ListFrame from "./ListFrame";
import TabFrame from "./TabFrame";

export default class DpWindow extends React.Component {
  render() {
    return <div className="dp-window">
      <Header />
      <AppSwitcher />
      <NavFrame />
      <div className="dp-content-outer-frame">
        <ListFrame />
        <TabFrame />
      </div>
    </div>;
  }
}
