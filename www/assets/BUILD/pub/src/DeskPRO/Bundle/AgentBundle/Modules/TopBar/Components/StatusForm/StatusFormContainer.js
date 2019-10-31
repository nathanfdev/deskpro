import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import StatusForm from './StatusForm';
import { toggleUserChat, editAgentProfile } from '../../../Agent/Actions/agentActions';
import { isVoiceEnabledSelector, isVoiceAvailableSelector } from '../../../Voice/Selectors/client';
import { userChatEnabledSelector } from '../../../Agent/Selectors/agents';
import { allQueuesSelector } from '../../../Voice/Selectors/queue';

@connect(state => ({
  me:              meSelector(state),
  voiceAvailable:  isVoiceAvailableSelector(state),
  voiceEnabled:    isVoiceEnabledSelector(state),
  userChatEnabled: userChatEnabledSelector(state),
  queues:          allQueuesSelector(state),
}))
class StatusFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onChange = (data) => {
    const { dispatch } = this.props;

    dispatch(toggleUserChat(data.chats));
    dispatch(editAgentProfile({
      available_status:         data.status,
      agent_calls_enabled:      data.calls,
      agent_can_use_forwarding: data.forwarding
    }));
  };

  openQueuesSettings = () => {
    window.AgentTopBar.closeUserMenu();
    setTimeout(() => {
      window.AgentVoiceDropdown.openSettingsTab();
    }, 1);
  };

  render() {
    return (
      <StatusForm
        {...this.props}
        onChange={this.onChange}
        openQueuesSettings={this.openQueuesSettings}
      />
    );
  }
}

export default StatusFormContainer;
