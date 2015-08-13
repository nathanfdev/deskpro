import React from "react";

import { connect } from 'react-redux';
import DpApp from "./DpApp";
import DpAppLoading from "./DpAppLoading";
import TicketsApp from '../../Tickets/Components/TicketsApp';
import TasksApp from '../../Tasks/Components/TasksApp';
import TestApp from '../../Test/Components/TestApp';
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

@connect((state) => {
  return {
    dp_window: state.Application.dp_window,
    routing: state.routing
  }
})
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
      let bp;
      if(base_end == -1) {
          bp = "/agent";
      } else {
        bp = DP_BASE_URL.substr(base_end) + "/agent";
      }

      console.log("base path: %s", bp);

      return bp;
  }

  render() {
    const { dp_window, history } = this.props;
    const base_path = this.workOutBasePath();

    if (dp_window.isLoaded) {
      return (
        <Router history={history}>
          <Route path={base_path} component={ReactRouterWrapper}>
            <Redirect from="/" to="tickets" />
            <Route name="tickets" path="tickets" component={TicketsApp} />
            <Route name="tasks" path="tasks" component={TasksApp} />
            <Route name="test" path="test" component={TestApp} />
          </Route>
        </Router>
      );
    } else {
      return <DpAppLoading />;
    }
  }
}
