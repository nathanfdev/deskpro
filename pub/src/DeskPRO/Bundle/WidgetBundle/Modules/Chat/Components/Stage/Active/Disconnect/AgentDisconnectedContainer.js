import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { AgentDisconnected } from './AgentDisconnected';
import { FindAnotherAgent } from './FindAnotherAgent';
import { WaitingLoader } from './WaitingLoader';
import {
  agentNameSelector,
  agentAvatarSelector,
  messagesSelector,
  lastMessageIdSelector,
  agentIdSelector,
} from '../../../../Selectors/chat';

@connect(state => ({
  agentId: agentIdSelector(state),
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state),
  messages: messagesSelector(state),
  lastMessageId: lastMessageIdSelector(state)
}))
export class AgentDisconnectedContainer extends React.Component {

  static propTypes = {
    agentId: PropTypes.number,
    agentName: PropTypes.string,
    agentAvatar: PropTypes.string,
    lastMessageId: PropTypes.number,
    messages: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      shown: false,
      started: false,
      lastMessageId: null,
      lastAgentName: props.agentName,
      lastAgentAvatar: props.agentAvatar
    };
  }

  componentDidUpdate() {
    if (this.state.shown) {
      this.checkForNewAgent();
    } else {
      this.checkForAgentTimeoutMessage();
    }
  }

  onStart = () => {
    this.setState({
      started: true
    });
  };

  checkForAgentTimeoutMessage() {
    const { messages, lastMessageId } = this.props;
    if (lastMessageId !== this.state.lastMessageId) {
      const agentTimeoutMessages = messages.filter(message => {
        return message.get('id') > this.state.lastMessageId
            && message.get('is_sys')
            && message.get('content').indexOf('message_agent-timeout') !== -1;
      });

      if (agentTimeoutMessages.size > 0) {
        this.setState({
          shown: true,
          started: true, // auto reassign for now
          lastMessageId: lastMessageId
        });
      } else {
        this.setState({
          lastMessageId: lastMessageId
        });
      }
    }
  }

  checkForNewAgent() {
    const { agentId, agentName, agentAvatar } = this.props;

    if (agentId) {
      this.setState({
        shown: false,
        started: false,
        lastAgentName: agentName,
        lastAgentAvatar: agentAvatar
      });
    }
  }

  render() {
    if (!this.state.shown) {
      return null;
    }

    return (
      <AgentDisconnected agentAvatar={this.state.lastAgentAvatar}>
        {this.state.started
          ? <WaitingLoader agentName={this.state.lastAgentName} />
          : <FindAnotherAgent onClick={this.onStart} agentName={this.state.lastAgentName} />
        }
      </AgentDisconnected>
    );
  }
}
