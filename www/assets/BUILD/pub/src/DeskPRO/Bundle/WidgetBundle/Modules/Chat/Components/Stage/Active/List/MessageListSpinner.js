import React from 'react';

export class MessageListSpinner extends React.Component {

  render() {
    return (
      <div className="circle-spinner-wrapper">
        <div className="circle-spinner central chat-message-list"><i /></div>
      </div>
    );
  }
}
