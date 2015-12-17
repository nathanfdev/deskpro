import React from 'react';

export class ReplyButtons extends React.Component {

  render() {
    return (
      <div className="preemtive-chat-footer">
        <div className="preemtive-chat-footer-button">
          <a href="#"><i className="fa fa-mail-reply-all"></i> Reply to Noelle</a>
          <a href="#" className="blank"><i className="fa fa-times"></i> Dismiss message</a>
        </div>
      </div>
    );
  }
}
