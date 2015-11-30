import React, { PropTypes } from 'react';

export class Controls extends React.Component {

  static propTypes = {
    ended: PropTypes.bool
  };

  render() {
    return (
      <div>
        <div className="dpdesignportal-chat-header-controls">
          <ul>
            <li>
              <a href="#" className="dpdesignportal-chat-header-control-item"><i className="fa fa-angle-double-left"></i>Assets</a>
            </li>
            <li>
              <a href="#" className="dpdesignportal-chat-header-control-item dpdesignportal-chat-header-control-mute"><i className="fa fa-volume-up"></i>Mute</a>
            </li>
            <li>
              <span className="dpdesignportal-checkbox-container dpdesignportal-chat-header-control-item">
                <span className="dpdesignportal-checkbox"><i className="fa fa-check"></i></span>
                Chat Transcript <i className="fa fa-exclamation-circle"></i>
              </span>
            </li>
            <li>
              <a href="#" className="dpdesignportal-chat-header-control-item">End Chat <i className="fa fa-power-off"></i></a>
            </li>
          </ul>
        </div>

        <div className="dpdesignportal-chat-header-controls">
          <ul>
            <li>
              <a href="#" className="dpdesignportal-chat-header-control-item"><i className="fa fa-angle-double-left"></i>Assets</a>
            </li>
            <li>
              <a href="#" className="dpdesignportal-chat-header-control-item">Reopen Chat <i className="fa fa-commenting-o"></i></a>
            </li>
          </ul>
        </div>
      </div>
    );
  }
}
