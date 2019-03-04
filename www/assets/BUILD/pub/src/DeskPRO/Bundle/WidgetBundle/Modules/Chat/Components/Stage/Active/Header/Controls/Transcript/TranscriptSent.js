import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class TranscriptSent extends React.Component {

  static propTypes = {
    email: PropTypes.string
  };

  render() {
    const { email } = this.props;

    return (
      <div className="dpdesignportal-popover-request-transcript-sent-message">
        {portalPhrases.get('portal.chat.transcript_already_sent', { email })}
      </div>
    );
  }
}
