import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

class BannedMessage extends React.Component {

  render() {
    return (
      <div className="inline-form-alert">
        <div className="form-alert-icon">
          <i className="fa fa-lock" />
        </div>
        <div className="content-wrapper">
          <h1>{portalPhrases.get('portal.chat.user_is_blocked')}</h1>
        </div>
      </div>
    );
  }
}

export default BannedMessage;
