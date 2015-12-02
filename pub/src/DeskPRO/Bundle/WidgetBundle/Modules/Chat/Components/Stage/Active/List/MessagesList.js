import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { RateAgentContainer } from './Feedback/RateAgentContainer';
import ScrollArea from 'react-scrollbar';

export class MessagesList extends React.Component {

  static propTypes = {
    messages: PropTypes.object,
    isEnded: PropTypes.bool
  };

  componentDidMount() {
    this.scrollBottom();
  }

  componentDidUpdate() {
    this.scrollBottom();
  }

  scrollBottom() {
    setTimeout(() => this.refs.scrollArea.scrollBottom(), 0);
  }

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
      <div className="dpdesignportal-content">
        <ScrollArea ref="scrollArea" vertical>
          <div className="bottom-aligner"/>
          <div>
            {messages.map((message, index) => this.renderMessage(message, index))}
          </div>
        </ScrollArea>

        {isEnded && <RateAgentContainer />}
      </div>
    );
  }
}
