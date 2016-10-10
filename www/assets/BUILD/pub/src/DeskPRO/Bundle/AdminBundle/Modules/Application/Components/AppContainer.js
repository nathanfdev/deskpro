import React, { PropTypes } from 'react';
import { Provider } from 'react-redux';
import { Router, Route } from 'react-router';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AdminBundle/DAL/config';
import store from '../../../Services/store';
import { history } from '../../../Services/history';
import TwilioAccounts from '../../Twilio/Components/Accounts/List/AccountListContainer';
import TwilioNumbers from '../../Twilio/Components/Numbers/List/NumberListContainer';
import TwilioAvailableNumbers from '../../Twilio/Components/Numbers/Search/AvailableNumbers/AvailableListContainer';
import TwilioExistingNumbers from '../../Twilio/Components/Numbers/Search/ExistingNumbers/ExistingListContainer';
import TwilioQueues from '../../Twilio/Components/Queues/List/QueueListContainer';
import TwilioNewQueue from '../../Twilio/Components/Queues/Form/NewQueueContainer';
import TwilioEditQueue from '../../Twilio/Components/Queues/Form/EditQueueContainer';

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
            <Route path="accounts" component={TwilioAccounts} />
            <Route path="numbers" component={TwilioNumbers} />
            <Route path="numbers/available" component={TwilioAvailableNumbers} />
            <Route path="numbers/existing" component={TwilioExistingNumbers} />
            <Route path="queues" component={TwilioQueues} />
            <Route path="queues/new" component={TwilioNewQueue} />
            <Route path="queues/:queueId" component={TwilioEditQueue} />
          </Route>
        </Router>
      </Provider>
    );
  }
}

export default AppContainer;
