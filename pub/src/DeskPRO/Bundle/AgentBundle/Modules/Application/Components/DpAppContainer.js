import React from "react";

import { connect } from 'redux/react';
import DpApp from "./DpApp";
import DpAppLoading from "./DpAppLoading";
import TicketsApp from '../../Tickets/Components/TicketsApp';
import TasksApp from '../../Tasks/Components/TasksApp';
import * as AppActions from "../Actions/AppActions";
import { Router, Route, Redirect } from 'react-router';

class ReactRouterWrapper extends React.Component {
  render() {
    return (
      <DpApp router={this.context.router}>
        {this.props.children}
      </DpApp>
    );
  }
}
ReactRouterWrapper.contextTypes = {
  router: React.PropTypes.object.isRequired
};

@connect(state => ({
  dp_window: state.dp_window,
  routing: state.routing,
}))
export default class DpAppContainer extends React.Component {
  constructor(props) {
    super(props);

    const { dp_window, dispatch } = this.props;

    if (!dp_window.isLoaded) {
      dispatch(AppActions.loadWindow());
    }
  }

  workOutBasePath() {
      let base_end = DP_BASE_URL.indexOf('/', DP_BASE_URL.indexOf('://') + 3);
      if(base_end == -1) {
          return "/agent";
      }

      return DP_BASE_URL.substr(base_end) + "/agent";
  }

  render() {
    const { dp_window, history } = this.props;
    const base_path = this.workOutBasePath();
    const default_path = `${base_path}/tickets`;

    if (dp_window.isLoaded) {
      return (
        <Router history={history}>
          <Redirect from={base_path} to={default_path} />
          <Route path={base_path} component={ReactRouterWrapper}>
            <Route name="tickets" path="tickets" component={TicketsApp} />
            <Route name="tasks" path="tasks" component={TasksApp} />
          </Route>
        </Router>
      );
    } else {
      return <DpAppLoading />;
    }
  }
}
