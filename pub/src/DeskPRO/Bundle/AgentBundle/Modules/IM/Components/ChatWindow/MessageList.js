import React from 'react';
import { Message } from './Message'

export class MessageList extends React.Component {
  render() {
    return (
      <ul className="chat-message-list">
        {this.props.messages && this.props.messages.length > 0 ? this.props.messages.map((message, index) => <Message
          key={index} message={message} agents={this.props.agents} me={this.props.me}/>) : null}
      </ul>
    );
  }
}