import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueueForm from './QueueForm';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { updateQueue, loadQueues, deleteQueue } from '../../../Actions/queueActions';
import { allQueuesSelector } from '../../../Selectors/queue';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';
import { loadAccounts } from '../../../Actions/accountActions';
import { replaceRoute } from '../../../../../Services/history';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { voicePeopleSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';

@connect(state => ({
  agents:         voicePeopleSelector(state),
  agentsLoaded:   isAgentsLoadedSelector(state),
  queues:         allQueuesSelector(state),
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state)
}))
class EditQueueContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch:       PropTypes.func,
    agents:         PropTypes.object,
    agentsLoaded:   PropTypes.bool,
    queues:         PropTypes.object,
    accounts:       PropTypes.object,
    accountsLoaded: PropTypes.bool,
    params:         PropTypes.object
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAccounts());
    dispatch(loadQueues());
  }

  onDelete = () => {
    const { dispatch } = this.props;
    const promise = dispatch(deleteQueue(this.getQueue().get('id')));
    promise.success(() => {
      replaceRoute('/voice_channel/queues');
    });
  };

  submitData = data => this.props.dispatch(updateQueue(this.getQueue().get('id'), data));

  getQueue() {
    const { params, queues } = this.props;
    const queueId = parseInt(params.queueId, 10);

    return queues && queues.get(queueId);
  }

  render() {
    const { agents, accounts, agentsLoaded, accountsLoaded } = this.props;
    const queue = this.getQueue();

    if (!queue || !agentsLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueForm
        {...this.state}
        queue={queue}
        agents={agents}
        accounts={accounts}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
        onReturnBack={this.onReturnBack}
      />
    );
  }
}

export default EditQueueContainer;
