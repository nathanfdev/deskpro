import React from "react";

import Header from "./Header";
import AppSwitcher from "./AppSwitcher";
import NavFrame from "./NavFrame";
import ListFrame from "./ListFrame";
import TabFrame from "./TabFrame";

import * as AppActions from "../Actions/AppActions";

import { bindActionCreators } from 'redux';
import { connect } from 'redux/react';

@connect(state => ({
  user: state.user
}))
export default class DpApp extends React.Component {
  render() {
    const { user, dispatch } = this.props;
    const actions = bindActionCreators(AppActions, dispatch);

    return <div className="dp-window">
        <Header user={user} actions={actions} />
        <AppSwitcher />
        <NavFrame />
        <div className="dp-content-outer-frame">
          <ListFrame />
          <TabFrame />
        </div>
      </div>;
  }
}
