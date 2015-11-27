import React from 'react';
import { WaitingPreview } from './WaitingPreview';

export class ChatWaiting extends React.Component {

  render() {
    return (
      <div>
        On waiting page:

        <WaitingPreview />
      </div>
    );
  }
}
