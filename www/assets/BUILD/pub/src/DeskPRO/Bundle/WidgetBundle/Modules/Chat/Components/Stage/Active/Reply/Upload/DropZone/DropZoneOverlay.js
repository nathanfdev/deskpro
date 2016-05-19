import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class DropZoneOverlay extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content-file-drop-active-container">
        <div className="dpdesignportal-content-file-drop-active">
          <span className="dpdesignportal-content-file-drop-mark">
            <i className="fa fa-upload" />
          </span>
          <p>
            <span>{portalPhrases.get('portal.chat.dropzone1')}</span>
            <span>{portalPhrases.get('portal.chat.dropzone2')}</span>
          </p>
        </div>
      </div>
    );
  }
}
