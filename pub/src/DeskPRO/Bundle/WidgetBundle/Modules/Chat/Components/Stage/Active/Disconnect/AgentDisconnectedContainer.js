import React from 'react';
import { connect } from 'react-redux';
import { agentNameSelector, agentAvatarSelector } from '../../../../Selectors/chat';
import { AgentDisconnected } from './AgentDisconnected';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state)
}))
export class AgentDisconnectedContainer extends React.Component {

  render() {
    return <AgentDisconnected {...this.props} />;
  }
}
