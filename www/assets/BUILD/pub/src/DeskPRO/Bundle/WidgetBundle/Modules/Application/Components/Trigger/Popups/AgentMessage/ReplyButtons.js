import React, { PropTypes } from 'react';

export class ReplyButtons extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor: PropTypes.string,
    primaryAgent: PropTypes.object,
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
    const { backgroundColor, textColor, primaryAgent } = this.props;
    const displayName = primaryAgent.get('display_name') || 'Agent';
    const firstName = displayName.split(' ')[0];

    return (
      <div className="preemtive-chat-footer">
        <div className="preemtive-chat-footer-button">
          <a href="#"
             onClick={this.onClick}
             style={{
               backgroundColor: backgroundColor,
               color: textColor
             }}>

            <i className="fa fa-mail-reply-all"></i> Reply to {firstName}
          </a>
          <a href="#" className="blank" onClick={this.onClose}><i className="fa fa-times"></i> Dismiss message</a>
        </div>
      </div>
    );
  }
}
