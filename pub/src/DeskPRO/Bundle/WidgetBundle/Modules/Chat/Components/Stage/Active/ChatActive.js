import React from 'react';
import { Header } from './Header';
import { MessagesList } from './MessagesList';
import { MessageInput } from './Reply/MessageInput';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <MessagesList />
        <MessageInput />
      </div>
    );
  }
}
