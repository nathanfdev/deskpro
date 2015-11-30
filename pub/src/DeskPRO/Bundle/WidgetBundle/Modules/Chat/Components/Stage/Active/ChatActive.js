import React from 'react';
import { Header } from './Header/Header';
import { MessagesListContainer } from './List/MessagesListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <MessagesListContainer />
        <ReplyFormContainer />
      </div>
    );
  }
}
