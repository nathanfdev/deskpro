import React, { PropTypes } from 'react';
import { Provider } from 'react-redux';
import { Router, Route } from 'react-router';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AdminBundle/DAL/config';
import store from '../../../Services/store';
import { history } from '../../../Services/history';
import * as Twilio from '../../Twilio/Components/index';

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
          <Route path="voice_channel">
            <Route path="accounts" component={Twilio.Accounts} />
            <Route path="numbers" component={Twilio.Numbers} />
            <Route path="numbers/available" component={Twilio.AvailableNumbers} />
            <Route path="numbers/existing" component={Twilio.ExistingNumbers} />
            <Route path="queues" component={Twilio.Queues} />
            <Route path="queues/new" component={Twilio.NewQueue} />
            <Route path="queues/:queueId" component={Twilio.EditQueue} />
            <Route path="extensions" component={Twilio.ExistingExtensionList} />
            <Route path="extensions/new" component={Twilio.NewExtensionList} />
          </Route>
        </Router>
      </Provider>
    );
  }
}

export default AppContainer;
