import React from 'react';

export class TransferedChatEvent extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-event">
        <div className="dpdesignportal-event-content">
          <span className="dpdesignportal-event-time">12.13pm</span>
          <hr/>
          <span className="dpdesignportal-event-title">
            <div className="dpdesignportal-event-title-avatar-container">
              <div className="dpdesignportal-message-avatar agent-left"><div className="dpdesignportal-message-avatar-meta negative"><i className="fa fa-minus"></i></div></div>
              <span className="dpdesignportal-message-avatar-relation"><i className="fa fa-long-arrow-right"></i></span>
              <div className="dpdesignportal-message-avatar"><div className="dpdesignportal-message-avatar-meta"><i className="fa fa-plus"></i></div></div>
            </div>
            Noelle Gray transferred the chat to dos lorem ipsum Terrance Reyes.
          </span>
        </div>
      </div>
    );
  }
}
