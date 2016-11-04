import React, { PropTypes } from 'react';
import { Provider } from 'react-redux';
import { Router, Route } from 'react-router';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AdminBundle/DAL/config';
import store from '../../../Services/store';
import { history } from '../../../Services/history';
import * as Voice from '../../Voice/Components/index';

class AppContainer extends React.Component {

  static propTypes = {
    routePath: PropTypes.string
  };

  constructor(props) {
    super(props);

    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);
  }

  componentWillMount() {
    history.replace(this.props.routePath);
  }

  render() {
    return (
      <Provider store={store}>
        <Router history={history}>
          {window.DP_HAS_VOICE &&
            <Route path="voice_channel">
              <Route path="accounts" component={Voice.Accounts} />
              <Route path="numbers" component={Voice.Numbers} />
              <Route path="numbers/available" component={Voice.AvailableNumbers} />
              <Route path="numbers/existing" component={Voice.ExistingNumbers} />
              <Route path="queues" component={Voice.Queues} />
              <Route path="queues/new" component={Voice.NewQueue} />
              <Route path="queues/:queueId" component={Voice.EditQueue} />
              <Route path="extensions" component={Voice.ExistingExtensionList} />
              <Route path="extensions/new" component={Voice.NewExtensionList} />
              <Route path="auto_attendants" component={Voice.AutoAttendantList} />
              <Route path="auto_attendants/new" component={Voice.NewAutoAttendant} />
              <Route path="auto_attendants/:autoAttendantId" component={Voice.EditAutoAttendant} />
              <Route path="agents" component={Voice.AgentsVoiceToggle} />
            </Route>}
        </Router>
      </Provider>
    );
  }
}

export default AppContainer;
