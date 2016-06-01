import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReplyButtons extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    primaryAgent:    PropTypes.object,
    onClick:         PropTypes.func,
    onClose:         PropTypes.func
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
          <a
            href="#"
            onClick={this.onClick}
            style={{
              backgroundColor,
              color: textColor
            }}
          >
            <i className="fa fa-mail-reply-all" />
            &nbsp;{portalPhrases.get('portal.chat.reply_to', { '{firstName}': firstName })}
          </a>
          <a href="#" className="blank" onClick={this.onClose}>
            <i className="fa fa-times" /> {portalPhrases.get('portal.chat.dismiss_message')}
          </a>
        </div>
      </div>
    );
  }
}
