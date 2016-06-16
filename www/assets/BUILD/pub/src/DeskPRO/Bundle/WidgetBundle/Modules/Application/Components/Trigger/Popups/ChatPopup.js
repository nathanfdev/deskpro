import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import $ from 'jquery';

export class ChatPopup extends React.Component {

  static propTypes = {
    small:          PropTypes.bool,
    widgetPosition: PropTypes.string,
    children:       PropTypes.any,
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

  onClose = event => {
    event.preventDefault();
    this.props.onClose();
  };

  updateInlineOffset() {
    const { popupStyle, getButton } = this.props;
    const $triggerFrame = $(parent.window.widget_trigger_iframe);
    const $popup = $(ReactDOM.findDOMNode(this.refs.popup), $triggerFrame);

    if (popupStyle === 'widget_button_agent') {
      const $button = $(ReactDOM.findDOMNode(getButton()), $triggerFrame).find('.preemtive-button');
      $popup.css('right', $button.width() + 10);
    } else {
      $popup.css('right', 0);
    }
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
        <div ref="popup" className={classNames('preemtive-chat', { small, 'position-left': leftPosition }, popupStyle)}>
          <a href="#" className="close-panel" onClick={this.onClose}>
            <i className="fa fa-times" />
          </a>
          {children}
        </div>
      </div>
    );
  }
}
