import React, { PropTypes } from 'react';
import SampleAvatar from '../../../../../Resources/img/sample-avatar.jpg';

export class OnlineAgentsPopup extends React.Component {

  static propTypes = {
    agents: PropTypes.object,
    onClick: PropTypes.func,
    onClose: PropTypes.func
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  onClose = event => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    return (
      <div className="dpdesignportal-state-buttons dpdesignportal-online-agents">
        <div className="preemtive-chat small">
          <span className="close-panel" onClick={this.onClose}>
            <i className="fa fa-close" />
          </span>
          <div className="preemtive-chat-content" onClick={this.onClick}>
            <h1>
              <span>Agents Online</span>
            </h1>
            <div className="dpdesignportal-chat-header">
              <div className="avatar-container">
                <ul className="multiple">
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar})`}} />
                  </li>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar})`}} />
                  </li>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar})`}} />
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <hr/>
          <div className="preemtive-chat-content">
            <div className="preemtive-chat-footer">
              <div className="preemtive-chat-footer-button">
                <a href="#" onClick={this.onClick} className="wide">
                  Start a conversation
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
