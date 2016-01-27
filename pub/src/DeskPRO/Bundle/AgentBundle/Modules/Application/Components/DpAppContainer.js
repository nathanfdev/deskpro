import React, { PropTypes } from 'react';
import { Router, Route, Redirect } from 'react-router';
import { connect } from 'react-redux';
import { DpAppRouteContainer } from './DpAppRouteContainer';
import { LoginRouteContainer } from '../../Login/Components/LoginRouteContainer';
import { TicketsApp } from '../../Tickets/Components/TicketsApp';
import { TasksApp } from '../../Tasks/Components/TasksApp';
import { FeedbackApp } from '../../Feedback/Components/FeedbackApp';
import { CrmApp } from '../../CRM/Components/CrmApp';
import { ChatApp } from '../../Chat/Components/ChatApp';
import { PublishApp } from '../../Publish/Components/PublishApp';
import { LoginApp } from '../../Login/Components/LoginApp';
import { ExampleApp } from '../../Example/Components/ExampleApp';
import { hashChanged } from '../../Application/Actions/routingActions';
import { setActiveApp } from '../../Application/Actions/appActions';
import { history } from '../../../Services/history';
import $ from 'jquery';

@connect()
export class DpAppContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentWillMount() {
    $.ajaxSetup({
      statusCode: {
        401: () => history.replace(`${DP_BASE_URL_RELATIVE}/${DP_AGENT_INTERFACE_PATH_NAMESPACE}/login`)
      }
    });
  }

  componentDidMount() {
    const { dispatch } = this.props;
    const myRe = new RegExp('\/'+DP_AGENT_INTERFACE_PATH_NAMESPACE+'\/(\w+)$');
    const myArr = myRe.exec(window.location.pathname);
    // dispatch setActiveApp() to store activeApp in Application.dpWindow.state
    dispatch(setActiveApp(myArr[1]));
    // dispatch hashChanged() when hash is changed to bind it to the redux state
    window.onhashchange = () => dispatch(hashChanged(window.location.hash));
    // dispatch hashChanged() to track the initial hash value
    dispatch(hashChanged(window.location.hash));
  }

  workOutBasePath() {
    const baseEnd = DP_BASE_URL.indexOf('/', DP_BASE_URL.indexOf('://') + 3);

    return (baseEnd !== -1 ? DP_BASE_URL.substr(baseEnd) : '') + '/' + DP_AGENT_INTERFACE_PATH_NAMESPACE;
  }

  render() {
    const basePath = this.workOutBasePath();
    const defaultPath = `${basePath}/tasks`;

    return (
      <Router history={history}>
        <Redirect from={basePath} to={defaultPath}/>
        <Route path={basePath} component={DpAppRouteContainer}>
          <Route name="crm" path="crm" component={CrmApp}/>
          <Route name="chat" path="chat" component={ChatApp}/>
          <Route name="tickets" path="tickets" component={TicketsApp}/>
          <Route name="tasks" path="tasks" component={TasksApp}/>
          <Route name="publish" path="publish" component={PublishApp}/>
          <Route name="feedback" path="feedback" component={FeedbackApp}/>
          <Route name="example" path="example" component={ExampleApp}/>
        </Route>
        <Route path={basePath} component={LoginRouteContainer}>
          <Route name="login" path="login" component={LoginApp}/>
        </Route>
      </Router>
    );
  }
}
