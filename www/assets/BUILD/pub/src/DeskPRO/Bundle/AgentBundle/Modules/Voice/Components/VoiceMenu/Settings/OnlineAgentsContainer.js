import React from 'react';
import { connect } from 'react-redux';
import OnlineAgents from './OnlineAgents';
import { voiceOnlineAgentsSelector } from '../../../Selectors/agents';

@connect(state => ({
  onlineAgents: voiceOnlineAgentsSelector(state)
}))
class OnlineAgentsContainer extends React.Component {

  render() {
    return <OnlineAgents {...this.props} />;
  }
}

export default OnlineAgentsContainer;
