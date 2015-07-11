import React from "react";

import { connect } from 'redux/react';
import DpApp from "./DpApp";
import DpAppLoading from "./DpAppLoading";

import * as AppActions from "../Actions/AppActions";

@connect(state => ({
  dp_window: state.dp_window
}))
export default class DpAppContainer extends React.Component {
  constructor(props) {
    super(props);

    const { dp_window, dispatch } = this.props;

    if (!dp_window.isLoaded) {
      dispatch(AppActions.loadWindow());
    }
  }

  render() {
    const { dp_window } = this.props;

    if (dp_window.isLoaded) {
      return <DpApp />;
    } else {
      return <DpAppLoading />;
    }
  }
}
