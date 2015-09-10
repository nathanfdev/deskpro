import React from 'react';
import { ChatCard } from './ChatCard';

export class ChatsList extends React.Component {

  render() {
    return (
      <div>
        {this.props.elements.map((element, index) => <ChatCard key={index} chat={element} />)}
      </div>
    );
  }

}