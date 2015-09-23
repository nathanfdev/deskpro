import React from 'react';
import Message from './Message'

export default class MessageList extends React.Component {
    render() {
        return (
            <ul className="chat-message-list">
                {this.props.messages.map((message, index) => <Message key={index} message={message} />)}
            </ul>
        );
    }
}