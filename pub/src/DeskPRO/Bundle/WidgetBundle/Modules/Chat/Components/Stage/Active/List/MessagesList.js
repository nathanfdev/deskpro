import React, { PropTypes } from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { TypingMessage } from './Message/TypingMessage';
import { RateAgent } from './Feedback/RateAgent';
import { ExtraRatingInfo } from './Feedback/ExtraRatingInfo';
import { RatingComplete } from './Feedback/RatingComplete';
import ScrollArea from 'react-scrollbar';
import Immutable from 'immutable';

export class MessagesList extends React.Component {

  static propTypes = {
    messages: PropTypes.object
  };

  renderMessage(message, index) {
    switch (message.get('type')) {
      case 'user':
        return <UserMessage key={index} message={message} />;
      case 'agent':
        return <AgentMessage key={index} message={message} />;
      default:
        return null;
    }
  }

  render() {
    return (
      <ScrollArea className="dpdesignportal-content" vertical>
        {this.props.messages.map((message, index) => this.renderMessage(message, index))}
        <TypingMessage user={Immutable.fromJS({name: 'Noelle'})} />

        <RateAgent />
        <ExtraRatingInfo />
        <RatingComplete />
      </ScrollArea>
    );
  }
}
