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
import { toggleVoiceEnabled, toggleOutboundCallsEnabled, toggleUseForwarding, toggleAll } from '../../Actions/agentActions';
import { settingsSelector, settingsLoadedSelector } from '../../Selectors/settings';
import { loadSettings, updateSettings } from '../../Actions/settingActions';
import { isTicketDepartmentsLoadedSelector, selectableTicketDepartmentsSelector } from '../../../Application/Selectors/departments';
import { loadSelectableTicketDepartments } from '../../../Application/Actions/departmentsActions';
import { allBrandsLoadedSelector, allBrandsSelector } from '../../../Application/Selectors/brands';
import { loadBrands } from '../../../Application/Actions/brandsActions';

@connect(state => ({
  accounts:                allAccountsSelector(state),
  accountsLoaded:          isAccountsLoadedSelector(state),
  agents:                  agentsSelector(state),
  isAgentsLoaded:          isAgentsLoadedSelector(state),
  settings:                settingsSelector(state),
  settingsLoaded:          settingsLoadedSelector(state),
  ticketDepartments:       selectableTicketDepartmentsSelector(state),
  ticketDepartmentsLoaded: isTicketDepartmentsLoadedSelector(state),
  brands:                  allBrandsSelector(state),
  brandsLoaded:            allBrandsLoadedSelector(state)
}))
class AgentsVoiceToggleContainer extends React.Component {

  static propTypes = {
    dispatch:                PropTypes.func,
    isAgentsLoaded:          PropTypes.bool,
    accountsLoaded:          PropTypes.bool,
    settingsLoaded:          PropTypes.bool,
    ticketDepartmentsLoaded: PropTypes.bool,
    brandsLoaded:            PropTypes.bool,
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
    dispatch(loadAgents());
    dispatch(loadSettings());
    dispatch(loadSelectableTicketDepartments(true));
    dispatch(loadBrands(true));
  }

  toggleEnabled = agent => this.props.dispatch(toggleVoiceEnabled(agent));
  toggleOutboundCalls = agent => this.props.dispatch(toggleOutboundCallsEnabled(agent));
  toggleUseForwarding = agent => this.props.dispatch(toggleUseForwarding(agent));
  toggleAll = () => this.props.dispatch(toggleAll());
  saveSettings = data => this.props.dispatch(updateSettings(data));
  goToAccounts = () => {
    replaceRoute('/voice_channel/accounts');
  };

  render() {
    const { isAgentsLoaded, accountsLoaded, settingsLoaded, ticketDepartmentsLoaded, brandsLoaded } = this.props;

    if (!isAgentsLoaded || !accountsLoaded || !settingsLoaded || !ticketDepartmentsLoaded || !brandsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AgentsVoiceToggle
        {...this.props}
        toggleAll={this.toggleAll}
        toggleEnabled={this.toggleEnabled}
        toggleOutboundCalls={this.toggleOutboundCalls}
        toggleUseForwarding={this.toggleUseForwarding}
        goToAccounts={this.goToAccounts}
        saveSettings={this.saveSettings}
      />
    );
  }
}

export default AgentsVoiceToggleContainer;
