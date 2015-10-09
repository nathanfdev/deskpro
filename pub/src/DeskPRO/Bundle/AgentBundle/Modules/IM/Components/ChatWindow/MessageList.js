import React from 'react';
import { Message } from './Message'

import { connect } from 'react-redux';
import { loadMessages, addMessage } from '../../Actions/imMessagesActions';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions'
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

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