import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentsSelector, isAgentsLoadedSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AgentsVoiceToggle from './AgentsVoiceToggle';
import { loadAgents } from '../../../Application/Actions/peopleActions';
import { replaceRoute } from '../../../../Services/history';
import { loadAccounts } from '../../Actions/accountActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../Selectors/account';
import { toggleVoiceEnabled, toggleOutboundCallsEnabled, toggleAll } from '../../Actions/agentActions';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  agents:         agentsSelector(state),
  isAgentsLoaded: isAgentsLoadedSelector(state)
}))
class AgentsVoiceToggleContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    isAgentsLoaded: PropTypes.bool,
    accountsLoaded: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
    dispatch(loadAgents());
  }

  onToggleEnabled = agent => this.props.dispatch(toggleVoiceEnabled(agent));
  onToggleOutboundCalls = agent => this.props.dispatch(toggleOutboundCallsEnabled(agent));
  onToggleAll = () => this.props.dispatch(toggleAll());

  onGoToAccounts = () => {
    replaceRoute('/voice_channel/accounts');
  };

  render() {
    const { isAgentsLoaded, accountsLoaded } = this.props;

    if (!isAgentsLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AgentsVoiceToggle
        {...this.props}
        onToggleAll={this.onToggleAll}
        onToggleEnabled={this.onToggleEnabled}
        onToggleOutboundCalls={this.onToggleOutboundCalls}
        onGoToAccounts={this.onGoToAccounts}
      />
    );
  }
}

export default AgentsVoiceToggleContainer;
