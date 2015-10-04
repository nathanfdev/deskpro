import React from 'react';
import Message from './Message'

export class MessageList extends React.Component {
  render() {
    return (
      <ul className="chat-message-list">
        {this.props.messages.length > 0 ? this.props.messages.map((message, index) => <Message key={index} message={message}/>) : null}
      </ul>
    );
  }
}