import React from 'react';
import { connect } from 'react-redux';
import { agentNameSelector, agentAvatarSelector, departmentNameSelector } from '../../../../../Selectors/chat';
import { OnlineAgent } from './OnlineAgent';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state),
  departmentName: departmentNameSelector(state)
}))
export class OnlineAgentContainer extends React.Component {

  render() {
    return <OnlineAgent {...this.props} />;
  }
}
