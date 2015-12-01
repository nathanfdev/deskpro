import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { TypingMessage } from './Message/TypingMessage';
import { TranscriptSent } from './Message/TranscriptSent';
import { RateAgentContainer } from './Feedback/RateAgentContainer';
import ScrollArea from 'react-scrollbar';
import Immutable from 'immutable';

export class MessagesList extends React.Component {

  static propTypes = {
    messages: PropTypes.object,
    isEnded: PropTypes.bool
  };

  renderMessage(message, index) {
    switch (message.get('type')) {
      case 'user':
        return <UserMessage key={index} message={message} />;
      case 'agent':
        return <AgentMessage key={index} message={message} />;
      default:
        return null;
    }
  }

  render() {
    const { messages, isEnded } = this.props;

    return (
      <div>
        <ScrollArea className="dpdesignportal-content" vertical>
          {messages.map((message, index) => this.renderMessage(message, index))}
          <TypingMessage user={Immutable.fromJS({name: 'Noelle'})} />
          <TranscriptSent />
        </ScrollArea>

        {isEnded && <RateAgentContainer />}
      </div>
    );
  }
}
