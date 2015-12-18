import React, { PropTypes } from 'react';
import SampleAvatar from '../../../../../Resources/img/sample-avatar.jpg';

export class OnlineAgentsPopup extends React.Component {

  static propTypes = {
    agents: PropTypes.object,
    onStart: PropTypes.func,
    onClose: PropTypes.func
  };

  onStart = event => {
    event.preventDefault();
    this.props.onStart();
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
          <div className="preemtive-chat-content">
            <h1>
              <span>Agents Online</span>
            </h1>
            <div className="dpdesignportal-chat-header">
              <div className="avatar-container">
                <ul className="multiple">
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar});`}} />
                  </li>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar});`}} />
                  </li>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar});`}} />
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <hr/>
          <div className="preemtive-chat-content">
            <div className="preemtive-chat-footer">
              <div className="preemtive-chat-footer-button">
                <a href="#" onClick={this.onStart} className="wide">
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
