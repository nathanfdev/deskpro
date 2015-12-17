import React from 'react';
import SampleAvatar from '../../../Resources/img/sample-avatar.jpg';

export class ChatMessagePopup extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-state-buttons">
        <div className="preemtive-chat">
          <div className="preemtive-chat-content">
            <div className="dpdesignportal-chat-header">
              <div className="avatar-container">
                <ul>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar});`}}></div>
                  </li>
                </ul>
                <hr/>
              </div>
              <h1><span>Noelle Gray</span></h1>
              <h2>DeskPRO Customer Support</h2>
              <p className="quote">
                Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.
              </p>
              </div>
            </div>
            <hr/>
            <div className="preemtive-chat-content">
              <div className="preemtive-chat-footer">
                <form>
                  <input type="text" placeholder="Reply" />
                  <button><i className="fa fa-angle-double-right"></i></button>
                </form>
                </div>
              </div>
            </div>

        <div className="preemtive-chat">
          <div className="preemtive-chat-content">
            <div className="dpdesignportal-chat-header">
              <div className="avatar-container">
                <ul>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar});`}}></div>
                  </li>
                </ul>
                <hr/>
              </div>
              <h1><span>Noelle Gray</span></h1>
              <h2>DeskPRO Customer Support</h2>
              <p className="quote">
                Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.
              </p>
            </div>
          </div>
          <hr/>
          <div className="preemtive-chat-content">
            <div className="preemtive-chat-footer">
              <div className="preemtive-chat-footer-button">
                <a href="#"><i className="fa fa-mail-reply-all"></i> Reply to Noelle</a>
                <a href="#" className="blank"><i className="fa fa-times"></i> Dismiss message</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
