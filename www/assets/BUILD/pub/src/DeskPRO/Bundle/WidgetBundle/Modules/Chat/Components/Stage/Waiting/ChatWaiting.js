import React from 'react';
import { Header } from '../Begin/Header';
import { ChatWaitingContainer } from './ChatWaitingContainer';

export class ChatWaiting extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <ChatWaitingContainer />
      </div>
    );
  }
}
