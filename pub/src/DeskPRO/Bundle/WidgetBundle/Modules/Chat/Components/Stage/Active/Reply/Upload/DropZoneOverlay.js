import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class DropZoneOverlay extends React.Component {

  static propTypes = {
    opened: PropTypes.bool
  };

  render() {
    return (
      <div className={classNames('dpdesignportal-content-file-drop-active-container', {'hidden': !this.props.opened})}>
        <div className="dpdesignportal-content-file-drop-active">
          <span className="dpdesignportal-content-file-drop-mark"><i className="fa fa-upload"></i></span>
          <p><span>Dropping this file here</span><span>Will send it as a message</span></p>
        </div>
      </div>
    );
  }
}
