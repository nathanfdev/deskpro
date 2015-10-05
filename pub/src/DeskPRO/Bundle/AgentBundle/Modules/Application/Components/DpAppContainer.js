import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { ReactRouterWrapper } from './ReactRouterWrapper';
import { DpAppLoading } from './DpAppLoading';
import TicketsApp from '../../Tickets/Components/TicketsApp';
import TasksApp from '../../Tasks/Components/TasksApp';
import TestApp from '../../Test/Components/TestApp';
import { FeedbackApp } from '../../Feedback/Components/FeedbackApp';
import { CrmApp } from '../../CRM/Components/CrmApp';
import { ChatApp } from '../../Chat/Components/ChatApp';
import { PublishApp } from '../../Publish/Components/PublishApp';
import * as AppActions from '../Actions/AppActions';
import { Router, Route, Redirect } from 'react-router';

@connect((state) => {
  return {
    dpWindow: state.Application.dpWindow,
    routing: state.Application.routing
  };
})
export class DpAppContainer extends React.Component {
  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    routing: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    history: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    const { dpWindow, dispatch } = this.props;

    if (!dpWindow.get('isLoaded')) {
      dispatch(AppActions.loadWindow());
    }
  }

  workOutBasePath() {
    const baseEnd = DP_BASE_URL.indexOf('/', DP_BASE_URL.indexOf('://') + 3);

    return (baseEnd !== -1 ? DP_BASE_URL.substr(baseEnd) : '') + '/agent';
  }

  render() {
    const { dpWindow, history } = this.props;

    if (!dpWindow.get('isLoaded')) {
      return <DpAppLoading />;
    }

    const basePath = this.workOutBasePath();
    const defaultPath = `${basePath}/tasks`;

    return (
      <Router history={history}>
        <Redirect from={basePath} to={defaultPath}/>
        <Route path={basePath} component={ReactRouterWrapper}>
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
  }
}
