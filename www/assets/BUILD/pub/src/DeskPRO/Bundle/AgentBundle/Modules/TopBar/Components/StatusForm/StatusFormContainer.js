import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import StatusForm from './StatusForm';
import { toggleUserChat, editAgentProfile } from '../../../Agent/Actions/agentActions';
import { isVoiceEnabledSelector, isVoiceAvailableSelector } from '../../../Voice/Selectors/client';
import { userChatEnabledSelector } from '../../../Agent/Selectors/agents';

@connect(state => ({
  me:              meSelector(state),
  voiceAvailable:  isVoiceAvailableSelector(state),
  voiceEnabled:    isVoiceEnabledSelector(state),
  userChatEnabled: userChatEnabledSelector(state)
}))
class StatusFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onChange = (data) => {
    const { dispatch } = this.props;

    dispatch(toggleUserChat(data.chats));
    dispatch(editAgentProfile({
      available_status:    data.status,
      agent_calls_enabled: data.calls
    }));
  };

  render() {
    return (
      <StatusForm
        {...this.props}
        onChange={this.onChange}
      />
    );
  }
}

export default StatusFormContainer;
