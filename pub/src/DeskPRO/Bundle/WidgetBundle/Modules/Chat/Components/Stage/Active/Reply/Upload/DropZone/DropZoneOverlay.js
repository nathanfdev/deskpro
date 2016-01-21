import React from 'react';

export class DropZoneOverlay extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content-file-drop-active-container">
        <div className="dpdesignportal-content-file-drop-active">
          <span className="dpdesignportal-content-file-drop-mark"><i className="fa fa-upload"></i></span>
          <p><span>Dropping this file here</span><span>Will send it as a message</span></p>
        </div>
      </div>
    );
  }
}
