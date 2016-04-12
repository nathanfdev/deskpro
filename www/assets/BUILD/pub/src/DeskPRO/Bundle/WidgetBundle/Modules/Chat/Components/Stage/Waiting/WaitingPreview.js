import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class WaitingPreview extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-collect-user-info-waiting">
        <div>
            {portalPhrases.get('portal.chat.message_wait-pending')}
        </div>
        <div className="spinner">
          <i />
        </div>
      </div>
    );
  }
}
