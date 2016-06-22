import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ReplyButtons extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    onClick:         PropTypes.func
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { backgroundColor, textColor } = this.props;

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
            {portalPhrases.get('portal.chat.start_conversation')}
          </a>
        </div>
      </div>
    );
  }
}
