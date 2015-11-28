import React from 'react';
import { Header } from './Header';
import { MessagesList } from './List/MessagesList';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <MessagesList />
        <ReplyFormContainer />
      </div>
    );
  }
}
