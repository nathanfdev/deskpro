import React from 'react';
import { Header } from './Header';
import { MessagesListContainer } from './List/MessagesListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';
import { EndChatConfirm } from './EndChatConfirm';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <MessagesListContainer />
        <ReplyFormContainer />

        <EndChatConfirm />
      </div>
    );
  }
}
