import React from 'react';
import { connect } from 'react-redux';
import { messagesSelector } from '../../../../Selectors/chat';
import { MessagesList } from './MessagesList';

@connect(state => ({
  messages: messagesSelector(state)
}))
export class MessagesListContainer extends React.Component {

  render() {
    return <MessagesList {...this.props} />;
  }
}
