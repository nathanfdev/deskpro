import React from 'react';

export class Header extends React.Component {

  render() {
    return (
      <div>
        <div className="dpdesignportal-chat-header">
          <div className="dpdesignportal-chat-header-avatar-container multiple">
            <ul>
              <li><div className="dpdesignportal-chat-header-avatar"></div></li>
              <li><div className="dpdesignportal-chat-header-avatar"></div></li>
              <li><div className="dpdesignportal-chat-header-avatar"></div></li>
            </ul>
          </div>
          <hr/>
          <h1>You are chatting with <span>Noelle Gray</span>, <span>Roland Holland</span> &amp; <span>Lester Rodriquez</span></h1>
          <h2>DeskPRO, Customer Support Representatives</h2>
        </div>

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
