import React from "react";

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import NavFrame from "./NavFrame";
import ListFrame from "./ListFrame";
import TabFrame from "./TabFrame";

import * as AppActions from "../Action/AppActions";

import { bindActionCreators } from 'redux';
import { connect } from 'redux/react';

@connect(state => ({
  User: state.User
}))
export default class DpApp extends React.Component {
  render() {
    const actions = bindActionCreators(AppActions, dispatch);
    const { User } = this.props.User;

    return
      <div className="dp-window">
        <Header user={User} actions={actions} />
        <AppSwitcher />
        <NavFrame />
        <div className="dp-content-outer-frame">
          <ListFrame />
          <TabFrame />
        </div>
      </div>;
  }
}
