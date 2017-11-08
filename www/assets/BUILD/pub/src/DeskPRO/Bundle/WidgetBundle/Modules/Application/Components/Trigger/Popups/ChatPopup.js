import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import $ from 'jquery';

export default class ChatPopup extends React.Component {

  static propTypes = {
    small:          PropTypes.bool,
    widgetPosition: PropTypes.string,
    children:       PropTypes.any, // eslint-disable-line react/forbid-prop-types
    liveDemo:       PropTypes.bool,
    onClose:        PropTypes.func,
    popupStyle:     PropTypes.string,
    getButton:      PropTypes.func
  };

  componentDidMount() {
    this.updateInlineOffset();
  }

  componentDidUpdate() {
    this.updateInlineOffset();
  }

  onClose = (event) => {
    event.preventDefault();
    this.props.onClose();
  };

  updateInlineOffset() {
    setTimeout(() => {
      const { popupStyle, getButton } = this.props;
      const $popup = $(this.popup);

      if (popupStyle === 'widget_button_agent') {
        const button = getButton();
        $popup.css('right', $(button).find('.preemtive-button').width() + 15);
      } else {
        $popup.css('right', 0);
      }
    }, 0);
  }

  render() {
    const { small, widgetPosition, children, liveDemo, popupStyle } = this.props;
    const parentWidth = $(parent.window.document).width();
    const hiddenPopup = !liveDemo && parentWidth < 760;
    const leftPosition = widgetPosition === 'bottom.left';

    return (
      <div
        className={classNames('dpdesignportal-state-buttons', {
          'dpdesignportal-agent-message': !small,
          'dpdesignportal-online-agents': small,
          'hidden-popup':                 hiddenPopup
        })}
      >
        <div
          ref={(node) => { this.popup = node; }}
          className={classNames('preemtive-chat', { small, 'position-left': leftPosition }, popupStyle)}
        >
          <a href="#close-panel" className="close-panel" onClick={this.onClose}>
            <i className="fa fa-times" />
          </a>
          {children}
        </div>
      </div>
    );
  }
}
