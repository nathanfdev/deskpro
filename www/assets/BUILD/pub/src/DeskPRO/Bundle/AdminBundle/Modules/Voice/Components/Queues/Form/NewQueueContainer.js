import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueuePageForm from './QueuePageForm';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { createQueue } from '../../../Actions/queueActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { allAgentsSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';

@connect(state => ({
  agents:         allAgentsSelector(state),
  agentsLoaded:   isAgentsLoadedSelector(state),
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state)
}))
class NewQueueContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch:       PropTypes.func,
    agents:         PropTypes.object,
    agentsLoaded:   PropTypes.bool,
    accounts:       PropTypes.object,
    accountsLoaded: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAccounts());
  }

  submitData = data => this.props.dispatch(createQueue(data));

  render() {
    const { agents, accounts, agentsLoaded, accountsLoaded } = this.props;

    if (!agentsLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueuePageForm
        {...this.state}
        agents={agents}
        accounts={accounts}
        onSubmit={this.onSubmit}
        onReturnBack={this.onReturnBack}
      />
    );
  }
}

export default NewQueueContainer;
