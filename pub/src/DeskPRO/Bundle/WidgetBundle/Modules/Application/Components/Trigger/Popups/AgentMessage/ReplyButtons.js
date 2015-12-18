import React, { PropTypes } from 'react';

export class ReplyButtons extends React.Component {

  static propTypes = {
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
      <div className="preemtive-chat-footer">
        <div className="preemtive-chat-footer-button">
          <a href="#" onClick={this.onClick}><i className="fa fa-mail-reply-all"></i> Reply to Noelle</a>
          <a href="#" className="blank" onClick={this.onClose}><i className="fa fa-times"></i> Dismiss message</a>
        </div>
      </div>
    );
  }
}
