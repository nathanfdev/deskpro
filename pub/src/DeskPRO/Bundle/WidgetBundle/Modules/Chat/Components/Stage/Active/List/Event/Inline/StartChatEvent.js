import React, { PropTypes } from 'react';
import { Event } from './Event';
import { MessageAvatar } from '../../Message/MessageAvatar';

export class StartChatEvent extends React.Component {

  static propTypes = {
    translatedText: PropTypes.string
  };

  render() {
    const { translatedText } = this.props;

    return (
      <Event {...this.props}>
        <span className="dpdesignportal-event-title">
          <MessageAvatar /> {translatedText}
        </span>
      </Event>
    );
  }
}
