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

  render() {
    const { dp_window, history } = this.props;
    
    if (dp_window.isLoaded) {
      return (
        <Router history={history}>
          <Route path="index.php/agent" component={ReactRouterWrapper}>
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
