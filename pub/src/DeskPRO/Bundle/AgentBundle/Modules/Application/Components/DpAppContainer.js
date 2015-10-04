import React from "react";

import { connect } from 'react-redux';
import DpApp from "./DpApp";
import DpAppLoading from "./DpAppLoading";
import TicketsApp from '../../Tickets/Components/TicketsApp';
import TasksApp from '../../Tasks/Components/TasksApp';
import TestApp from '../../Test/Components/TestApp';
import { FeedbackApp } from '../../Feedback/Components/FeedbackApp';
import { CrmApp } from '../../CRM/Components/CrmApp';
import { ChatApp } from '../../Chat/Components/ChatApp';
import { PublishApp } from '../../Publish/Components/PublishApp';
import * as AppActions from "../Actions/AppActions";
import { Router, Route, Redirect } from 'react-router';

class ReactRouterWrapper extends React.Component {
  render() {
    return (
      <DpApp>
        {this.props.children}
      </DpApp>
    );
  }
}

@connect((state) => {
  return {
    dpWindow: state.Application.dpWindow,
    routing: state.Application.routing
  };
})
export default class DpAppContainer extends React.Component {
  constructor(props) {
    super(props);

    const { dpWindow, dispatch } = this.props;

    if (!dpWindow.get('isLoaded')) {
      dispatch(AppActions.loadWindow());
    }
  }

  workOutBasePath() {
    let base_end = DP_BASE_URL.indexOf('/', DP_BASE_URL.indexOf('://') + 3);
    let bp;
    if (base_end === -1) {
      bp = '/agent';
    } else {
      bp = DP_BASE_URL.substr(base_end) + '/agent';
    }

    return bp;
  }

  render() {
    const { dpWindow, history } = this.props;
    const base_path = this.workOutBasePath();
    const default_path = `${base_path}/tasks`;

    if (dpWindow.get('isLoaded')) {
      return (
        <Router history={history}>
          <Redirect from={base_path} to={default_path}/>
          <Route path={base_path} component={ReactRouterWrapper}>
            <Route name="crm" path="crm" component={CrmApp}/>
            <Route name="chat" path="chat" component={ChatApp}/>
            <Route name="tickets" path="tickets" component={TicketsApp}/>
            <Route name="tasks" path="tasks" component={TasksApp}/>
            <Route name="publish" path="publish" component={PublishApp}/>
            <Route name="feedback" path="feedback" component={FeedbackApp}/>
            <Route name="test" path="test" component={TestApp}/>
          </Route>
        </Router>
      );
    } else {
      return <DpAppLoading />;
    }
  }
}
