import React, { PropTypes } from 'react';
import { Router, Route, Redirect } from 'react-router';
import { connect } from 'react-redux';
import { ReactRouterWrapper } from './ReactRouterWrapper';
import { DpAppLoading } from './DpAppLoading';
import TicketsApp from '../../Tickets/Components/TicketsApp';
import TasksApp from '../../Tasks/Components/TasksApp';
import { FeedbackApp } from '../../Feedback/Components/FeedbackApp';
import { CrmApp } from '../../CRM/Components/CrmApp';
import { ChatApp } from '../../Chat/Components/ChatApp';
import { PublishApp } from '../../Publish/Components/PublishApp';
import { LoginApp } from '../../Login/Components/LoginApp';
import { WelcomeApp } from '../../Welcome/Components/WelcomeApp';
import { ExampleApp } from '../../Example/Components/ExampleApp';
import { loadMe } from '../RecordStores/Actions/meActions';
import { meStateSelector } from '../RecordStores/Selectors/meSelectors';
import { hashChanged } from '../../Application/Actions/routingActions';
import Jquery from 'jquery';

@connect(state => ({
  userStatus: meStateSelector.statusSel(state)
}))
export class DpAppContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    history: PropTypes.object.isRequired,
    userStatus: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(loadMe());

    Jquery.ajaxSetup({
      statusCode: {
        401: function() {
          if (window.location.pathname !== '/agent/login') {
            window.location.href = '/agent/login';
          }
        }
      }
    });
  }

  workOutBasePath() {
    const baseEnd = DP_BASE_URL.indexOf('/', DP_BASE_URL.indexOf('://') + 3);

    return (baseEnd !== -1 ? DP_BASE_URL.substr(baseEnd) : '') + '/agent';
  }

  render() {
    const { userStatus, history, dispatch } = this.props;

    // dispatch hashChanged() when hash is changed to bind it to the redux state
    window.onhashchange = () => dispatch(hashChanged(window.location.hash));

    // dispatch hashChanged() to track the initial hash value
    dispatch(hashChanged(window.location.hash));

    if (userStatus.get('isLoading')) {
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
          <Route name="example" path="example" component={ExampleApp}/>
        </Route>
        <Route path={basePath}>
          <Route name="login" path="login" component={LoginApp}/>
          <Route name="welcome" path="welcome" component={WelcomeApp}/>
        </Route>
      </Router>
    );
  }
}
