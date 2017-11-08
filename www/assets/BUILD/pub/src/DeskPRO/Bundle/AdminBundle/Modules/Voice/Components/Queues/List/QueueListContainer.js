import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueueList from './QueueList';
import { replaceRoute } from '../../../../../Services/history';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { allAgentsSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  agents:         allAgentsSelector(state),
  agentsLoaded:   isAgentsLoadedSelector(state),
  queues:         allQueuesSelector(state),
  queuesLoaded:   isQueuesLoadedSelector(state)
}))
class QueueListContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accounts:       PropTypes.object,
    agents:         PropTypes.object,
    agentsLoaded:   PropTypes.bool,
    queues:         PropTypes.object,
    queuesLoaded:   PropTypes.bool,
    accountsLoaded: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
    dispatch(loadAgents());
    dispatch(loadQueues());
  }

  onGoToAccounts = () => {
    replaceRoute('/voice_channel/accounts');
  };

  onAddQueue = (event) => {
    event.preventDefault();
    replaceRoute('/voice_channel/queues/new');
  };

  onEditQueue = (queue) => {
    replaceRoute(`/voice_channel/queues/${queue.get('id')}`);
  };

  render() {
    const { queuesLoaded, accountsLoaded, agentsLoaded, accounts, queues, agents } = this.props;

    if (!queuesLoaded || !accountsLoaded || !agentsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueList
        accounts={accounts}
        queues={queues}
        agents={agents}
        onAddQueue={this.onAddQueue}
        onEditQueue={this.onEditQueue}
        onGoToAccounts={this.onGoToAccounts}
      />
    );
  }
}

export default QueueListContainer;
