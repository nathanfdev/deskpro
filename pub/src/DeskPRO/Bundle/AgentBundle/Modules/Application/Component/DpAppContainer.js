import React from "react";

import { connect } from 'redux/react';
import DpApp from "./DpApp";
import DpAppLoading from "./DpAppLoading";

import * as AppActions from "../Action/AppActions";

@connect(state => ({
  DpWindow: state.DpWindow
}))
export default class DpAppContainer extends React.Component {
  constructor(props) {
    super(props);

    const { DpWindow, dispatch } = this.props;

    if (!DpWindow.isLoaded) {
      dispatch(AppActions.loadWindow());
    }
  }

  render() {
    const { DpWindow } = this.props;

    if (DpWindow.isLoaded) {
      return <DpApp />;
    } else {
      return <DpAppLoading />;
    }
  }
}
