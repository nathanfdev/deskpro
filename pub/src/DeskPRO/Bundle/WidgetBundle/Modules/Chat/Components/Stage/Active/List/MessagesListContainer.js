import React from 'react';
import { connect } from 'react-redux';
import { messagesSelector, isEndedSelector } from '../../../../Selectors/chat';
import { MessagesList } from './MessagesList';

@connect(state => ({
  messages: messagesSelector(state),
  isEnded: isEndedSelector(state)
}))
export class MessagesListContainer extends React.Component {

  render() {
    return <MessagesList {...this.props} />;
  }
}
