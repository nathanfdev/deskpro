import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { AgentDisconnected } from './AgentDisconnected';
import { FindAnotherAgent } from './FindAnotherAgent';
import { WaitingLoader } from './WaitingLoader';
import {
  agentNameSelector,
  agentAvatarSelector,
  messagesSelector,
  lastMessageIdSelector
} from '../../../../Selectors/chat';

@connect(state => ({
  agentName: agentNameSelector(state),
  agentAvatar: agentAvatarSelector(state),
  messages: messagesSelector(state),
  lastMessageId: lastMessageIdSelector(state)
}))
export class AgentDisconnectedContainer extends React.Component {

  static propTypes = {
    lastMessageId: PropTypes.number,
    messages: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      shown: false,
      started: false,
      lastMessageId: null
    };
  }

  componentDidUpdate() {
    this.checkForAgentTimeoutMessage();
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
          lastMessageId: lastMessageId
        });
      } else {
        this.setState({
          lastMessageId: lastMessageId
        });
      }
    }
  }

  render() {
    if (!this.state.shown) {
      return null;
    }

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
