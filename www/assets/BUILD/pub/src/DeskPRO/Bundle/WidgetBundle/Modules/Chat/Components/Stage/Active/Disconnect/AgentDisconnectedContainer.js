import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { AgentDisconnected } from './AgentDisconnected';
import { FindAnotherAgent } from './FindAnotherAgent';
import { WaitingLoader } from './WaitingLoader';
import { agentNameSelector, agentAvatarSelector, agentIdSelector } from '../../../../Selectors/chat';

@connect(state => ({
  agentId:     agentIdSelector(state),
  agentName:   agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state)
}))
export class AgentDisconnectedContainer extends React.Component {

  static propTypes = {
    agentId:     PropTypes.number,
    agentName:   PropTypes.string,
    agentAvatar: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      started: !props.agentId
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.agentId) {
      this.setState({
        started: false
      });
    } else {
      this.setState({
        started: true // auto reassign for now
      });
    }
  }

  onStart = () => {
    this.setState({
      started: true
    });
  };

  render() {
    const { agentId, agentName, agentAvatar } = this.props;

    if (agentId) {
      return null;
    }

    return (
      <AgentDisconnected agentAvatar={agentAvatar}>
        {this.state.started
          ? <WaitingLoader agentName={agentName} />
          : <FindAnotherAgent onClick={this.onStart} agentName={agentName} />
        }
      </AgentDisconnected>
    );
  }
}
