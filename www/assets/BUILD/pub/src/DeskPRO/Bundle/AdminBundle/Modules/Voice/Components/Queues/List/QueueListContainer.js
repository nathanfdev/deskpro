import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueueList from './QueueList';
import { replaceRoute } from '../../../../../Services/history';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadQueues } from '../../../Actions/queueActions';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  queues:         allQueuesSelector(state),
  queuesLoaded:   isQueuesLoadedSelector(state)
}))
class QueueListContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accounts:       PropTypes.object,
    queues:         PropTypes.object,
    queuesLoaded:   PropTypes.bool,
    accountsLoaded: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
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
    const { queuesLoaded, accountsLoaded, accounts, queues } = this.props;

    if (!queuesLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueList
        accounts={accounts}
        queues={queues}
        onAddQueue={this.onAddQueue}
        onEditQueue={this.onEditQueue}
        onGoToAccounts={this.onGoToAccounts}
      />
    );
  }
}

export default QueueListContainer;
