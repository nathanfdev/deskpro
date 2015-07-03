import React from "react";

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import NavFrame from "./NavFrame";
import ListFrame from "./ListFrame";
import TabFrame from "./TabFrame";

import * as AppActions from "../Action/AppActions";

import { bindActionCreators } from 'redux';
import { Connector } from 'redux/react';

export default class DpWindow extends React.Component {
  render() {
    return <div className="dp-window">
      <Connector select={(state) => { return { user: state.App.user}}}>
        { ({user, dispatch}) => {
          return <Header user={user} {...bindActionCreators(AppActions, dispatch)} />
        }}
      </Connector>
      <AppSwitcher />
      <NavFrame />
      <div className="dp-content-outer-frame">
        <ListFrame />
        <TabFrame />
      </div>
    </div>;
  }
}
