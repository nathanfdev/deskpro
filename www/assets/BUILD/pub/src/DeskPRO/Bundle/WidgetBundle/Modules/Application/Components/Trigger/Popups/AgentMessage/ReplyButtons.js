import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReplyButtons extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    primaryAgent:    PropTypes.object,
    onClick:         PropTypes.func
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
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
            {portalPhrases.get('portal.chat.start_conversation', { '{firstName}': firstName })}
          </a>
        </div>
      </div>
    );
  }
}
