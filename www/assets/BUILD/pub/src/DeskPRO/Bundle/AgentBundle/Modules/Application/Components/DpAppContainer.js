import React from 'react';
import PropTypes from 'prop-types';
import { Router, Route, Redirect } from 'react-router';
import { connect } from 'react-redux';
import $ from 'jquery';
import { preloadData } from '../../Application/Actions/bootstrapActions';
import { DpAppRouteContainer } from './DpAppRouteContainer';
import { CrmApp } from '../../CRM/Components/CrmApp';
import { ChatApp } from '../../Chat/Components/ChatApp';
import { PublishApp } from '../../Publish/Components/PublishApp';
import { LoginApp } from '../../Login/Components/LoginApp';
import { hashChanged } from '../../Application/Actions/routingActions';
import { setActiveApp } from '../../Application/Actions/appActions';
import { history } from '../../../Services/history';

@connect()
export class DpAppContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentWillMount() {
    $.ajaxSetup(
      {
        statusCode: {
          401: () => history.replace(`${window.DP_BASE_URL_RELATIVE}/${window.DP_AGENT_INTERFACE_PATH_NAMESPACE}/login`)
        }
      }
    );
  }

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(preloadData());

    // dispatch setActiveApp() to store activeApp in Application.dpWindow.state
    const urlParts = window.location.pathname.split('/');
    if (urlParts.length > 1) {
      dispatch(setActiveApp(urlParts[2]));
    }

    // dispatch hashChanged() when hash is changed to bind it to the redux state
    window.onhashchange = () => dispatch(hashChanged(window.location.hash));

    // dispatch hashChanged() to track the initial hash value
    dispatch(hashChanged(window.location.hash));
  }

  workOutBasePath() {
    const baseEnd = window.DP_BASE_URL.indexOf('/', window.DP_BASE_URL.indexOf('://') + 3);

    return `${baseEnd !== -1 ? window.DP_BASE_URL.substr(baseEnd) : ''}/${window.DP_AGENT_INTERFACE_PATH_NAMESPACE}`;
  }

  render() {
    const basePath    = this.workOutBasePath();
    const defaultPath = `${basePath}/tasks`;

    return (
      <Router history={history}>
        <Redirect from={basePath} to={defaultPath} />
        <Route path={basePath} component={DpAppRouteContainer}>
          <Route name="crm" path="crm" component={CrmApp} />
          <Route name="chat" path="chat" component={ChatApp} />
          <Route name="publish" path="publish" component={PublishApp} />
        </Route>
        <Route path={basePath}>
          <Route name="login" path="login" component={LoginApp} />
        </Route>
      </Router>
    );
  }
}
