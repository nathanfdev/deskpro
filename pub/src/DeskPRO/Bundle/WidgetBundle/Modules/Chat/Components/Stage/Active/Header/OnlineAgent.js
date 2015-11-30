import React from 'react';

export class OnlineAgent extends React.Component {

  render() {
    return (
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
    );
  }
}
