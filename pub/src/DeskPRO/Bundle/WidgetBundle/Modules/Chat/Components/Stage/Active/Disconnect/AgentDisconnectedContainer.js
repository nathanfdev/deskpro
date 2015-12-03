import React from 'react';
import { connect } from 'react-redux';
import { agentNameSelector, agentAvatarSelector } from '../../../../Selectors/chat';
import { AgentDisconnected } from './AgentDisconnected';
import { FindAnotherAgent } from './FindAnotherAgent';
import { WaitingLoader } from './WaitingLoader';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state)
}))
export class AgentDisconnectedContainer extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      started: false
    };
  }

  onStart = () => {
    this.setState({
      started: true
    });
  };

  render() {
    return (
      <AgentDisconnected {...this.props}>
        {this.state.started
          ? <WaitingLoader />
          : <FindAnotherAgent onClick={this.onStart} {...this.props} />
        }
      </AgentDisconnected>
    );
  }
}
