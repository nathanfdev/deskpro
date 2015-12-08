import React, { PropTypes } from 'react';
import { Event } from './Event';

export class JoinedEvent extends React.Component {

  static propTypes = {
    content: PropTypes.object,
    translatedText: PropTypes.string
  };

  render() {
    const { content, translatedText } = this.props;

    return (
      <Event {...this.props}>
        <span className="dpdesignportal-event-title">
          {translatedText.replace('{{name}}', content.name)}
        </span>
      </Event>
    );
  }
}
