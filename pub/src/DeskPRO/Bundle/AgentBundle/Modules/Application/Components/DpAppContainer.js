import React, { PropTypes } from 'react';
import { Router, Route, Redirect } from 'react-router';
import { connect } from 'react-redux';
import { ReactRouterWrapper } from './ReactRouterWrapper';
import { DpAppLoading } from './DpAppLoading';
import { FeedbackApp } from '../../Feedback/Components/FeedbackApp';
import { CrmApp } from '../../CRM/Components/CrmApp';
import { ChatApp } from '../../Chat/Components/ChatApp';
import { PublishApp } from '../../Publish/Components/PublishApp';
import TicketsApp from '../../Tickets/Components/TicketsApp';
import TasksApp from '../../Tasks/Components/TasksApp';
import TestApp from '../../Test/Components/TestApp';
import * as AppActions from '../../Application/Actions/AppActions';
import { hashChanged } from '../../Application/Actions/routingActions';


@connect((state) => ({
  dpWindow: state.Application.dpWindow
}))
export class DpAppContainer extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
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
    const { dpWindow, history, dispatch } = this.props;

    // dispatch hashChanged() when hash is changed to bind it to the redux state
    window.onhashchange = () => dispatch(hashChanged(window.location.hash));

    // dispatch hashChanged() to track the initial hash value
    dispatch(hashChanged(window.location.hash));

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
