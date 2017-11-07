import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { agentsSelector, isAgentsLoadedSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AgentsVoiceToggle from './AgentsVoiceToggle';
import { loadAgents } from '../../../Application/Actions/peopleActions';
import { replaceRoute } from '../../../../Services/history';
import { loadAccounts } from '../../Actions/accountActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../Selectors/account';
import { toggleVoiceEnabled, toggleOutboundCallsEnabled, toggleAll } from '../../Actions/agentActions';
import { settingsSelector, settingsLoadedSelector } from '../../Selectors/settings';
import { loadSettings, updateSettings } from '../../Actions/settingActions';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  agents:         agentsSelector(state),
  isAgentsLoaded: isAgentsLoadedSelector(state),
  settings:       settingsSelector(state),
  settingsLoaded: settingsLoadedSelector(state)
}))
class AgentsVoiceToggleContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    isAgentsLoaded: PropTypes.bool,
    accountsLoaded: PropTypes.bool,
    settingsLoaded: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
    dispatch(loadAgents());
    dispatch(loadSettings());
  }

  onToggleEnabled = agent => this.props.dispatch(toggleVoiceEnabled(agent));
  onToggleOutboundCalls = agent => this.props.dispatch(toggleOutboundCallsEnabled(agent));
  onToggleAll = () => this.props.dispatch(toggleAll());
  onSaveSettings = data => this.props.dispatch(updateSettings(data));

  onGoToAccounts = () => {
    replaceRoute('/voice_channel/accounts');
  };

  render() {
    const { isAgentsLoaded, accountsLoaded, settingsLoaded } = this.props;

    if (!isAgentsLoaded || !accountsLoaded || !settingsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AgentsVoiceToggle
        {...this.props}
        onToggleAll={this.onToggleAll}
        onToggleEnabled={this.onToggleEnabled}
        onToggleOutboundCalls={this.onToggleOutboundCalls}
        onGoToAccounts={this.onGoToAccounts}
        onSaveSettings={this.onSaveSettings}
      />
    );
  }
}

export default AgentsVoiceToggleContainer;
