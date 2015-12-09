import React from 'react';
import { Header } from '../Begin/Header';
import { WaitingPreview } from './WaitingPreview';

export class ChatWaiting extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <WaitingPreview />
      </div>
    );
  }
}
