import PropTypes from 'prop-types';
import React from 'react';
import { Provider } from 'react-redux';
import { Router, Route } from 'react-router';
import toastr from 'toastr';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AdminBundle/DAL/config';
import store from '../../../Services/store';
import { history } from '../../../Services/history';
import * as EmailTemplates from '../../EmailTemplates/Components';
import * as Voice from '../../Voice/Components/index';
import * as Dev from '../../Dev/Components/index';
import * as Apps from '../../Apps/Components/index';
import { loadAdminPhraseTranslations } from '../Actions/bootstrapActions';
import { InstallerFactory } from '../../DeskproApps';

class AppContainer extends React.Component {

  static propTypes = {
    routePath:      PropTypes.string,
    legacyNavigate: PropTypes.func
  };

  constructor(props) {
    super(props);

    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);

    store.dispatch(loadAdminPhraseTranslations());
  }

  componentWillMount() {
    history.replace(this.props.routePath);
    toastr.options = {
      closeButton:     true,
      positionClass:   'toast-bottom-right',
      showDuration:    250,
      hideDuration:    500,
      timeOut:         3000,
      extendedTimeOut: 600,
      showEasing:      'swing',
      hideEasing:      'linear',
      showMethod:      'slideDown',
      hideMethod:      'fadeOut'
    };
  }

  render() {
    const props = this.props;
    return (
      <Provider store={store}>
        <Router history={history}>
          {window.DP_HAS_VOICE ?
            <Route path="voice_channel">
              <Route path="accounts" component={Voice.Accounts} />
              <Route path="numbers" component={Voice.Numbers} />
              <Route path="numbers/available" component={Voice.AvailableNumbers} />
              <Route path="numbers/existing" component={Voice.ExistingNumbers} />
              <Route path="numbers/new" component={Voice.NewNumber} />
              <Route path="numbers/:numberId" component={Voice.EditNumber} />
              <Route path="queues" component={Voice.Queues} />
              <Route path="queues/new" component={Voice.NewQueue} />
              <Route path="queues/:queueId" component={Voice.EditQueue} />
              <Route path="extensions" component={Voice.ExistingExtensionList} />
              <Route path="extensions/new" component={Voice.NewExtensionList} />
              <Route path="extensions/:agentId" component={Voice.EditExtension} />
              <Route path="auto_attendants" component={Voice.AutoAttendantList} />
              <Route path="auto_attendants/new" component={Voice.NewAutoAttendant} />
              <Route path="auto_attendants/:autoAttendantId" component={Voice.EditAutoAttendant} />
              <Route path="agents" component={Voice.AgentsVoiceToggle} />
              <Route path="call_logs" component={Voice.CallLogsList} />
              <Route path="call_logs/:callId" component={Voice.CallLogView} />
            </Route> : null}
          {window.DP_HAS_DEV ?
            <Route path="dev">
              <Route path="notifications" component={Dev.Notifications} />
            </Route> : null}
          <Route path="apps">
            <Route path="oauth_clients" component={Apps.OAuthClientList} />
            <Route path="oauth_clients/new" component={Apps.NewOAuthClientForm} />
            <Route path="oauth_clients/:clientId" component={Apps.EditOAuthClientForm} />
            <Route path="importer" component={Apps.ImporterContainer} />
            <Route path="importer/status" component={Apps.ImporterStatusContainer} />
            <Route path="importer/source/:type" component={Apps.ImporterSourceContainer} />
          </Route>
          <Route
            path="app-install/:installType/:app"
            getComponent={(nextState, cb) => cb(null, InstallerFactory.routeFactory({
              windowObject:   window.parent || window,
              legacyNavigate: this.props.legacyNavigate
            }))}
          />
          <Route path="emails" key="email_routes">
            <Route path="templates_editor(/:name)" component={EmailTemplates.EmailTemplatesEditorContainer} {...props} />
          </Route>
        </Router>
      </Provider>
    );
  }
}

export default AppContainer;
