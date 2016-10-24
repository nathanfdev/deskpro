import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    chatId:          PropTypes.string,
    widgetPosition:  PropTypes.string,
    type:            PropTypes.string,
    onClick:         PropTypes.func,
    size:            PropTypes.string,
    name:            PropTypes.string,
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    disabled:        PropTypes.bool,
    triggerResize:   PropTypes.func,
    locationPath:    PropTypes.string
  };

  componentDidUpdate() {
    this.props.triggerResize();
  }

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { chatId, widgetPosition, name, size, disabled, backgroundColor, textColor, locationPath } = this.props;

    let buttonCaption;
    if (chatId && locationPath === '/chat/active') {
      buttonCaption = portalPhrases.get('portal.chat.reopen_chat_action');
    } else {
      buttonCaption = name;
    }

    return (
      <div className="dpdesignportal-state-buttons">
        <a
          href="#"
          onClick={this.onClick}
          style={{
            backgroundColor,
            color: textColor
          }}
          className={classNames('preemtive-button', {
            disabled,
            'button-s':      size === 'small',
            'button-l':      size === 'large',
            'position-left': widgetPosition === 'bottom.left'
          })}
        >
          <span className="state-button-text">{buttonCaption}</span>
          <span
            className="state-button-icon"
            style={{
              color:      backgroundColor,
              background: textColor
            }}
          >
            <span>?</span>
          </span>
        </a>
      </div>
    );
  }
}
