import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    chatId:          PropTypes.string,
    widgetPosition:  PropTypes.string,
    onClick:         PropTypes.func,
    size:            PropTypes.string,
    name:            PropTypes.string,
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    disabled:        PropTypes.bool,
    triggerResize:   PropTypes.func,
    widgetOpened:    PropTypes.bool
  };

  componentDidUpdate() {
    this.props.triggerResize();
  }

  onClick = (event) => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { chatId, widgetPosition, name, size, disabled, backgroundColor, textColor, widgetOpened } = this.props;

    let buttonCaption;
    if (chatId && !widgetOpened) {
      buttonCaption = portalPhrases.get('portal.chat.reopen_chat_action');
    } else {
      buttonCaption = name;
    }

    return (
      <div className="dpdesignportal-state-buttons" ref={(node) => { this.node = node; }}>
        <a
          href="#open-widget"
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
