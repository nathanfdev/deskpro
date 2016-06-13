import React, { PropTypes } from 'react';
import classNames from 'classnames';
import $ from 'jquery';

export class ChatPopup extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    small:           PropTypes.bool,
    widgetPosition:  PropTypes.string,
    children:        PropTypes.any,
    liveDemo:        PropTypes.bool,
    onClose:         PropTypes.func
  };

  onClose = event => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    const { small, widgetPosition, backgroundColor, children, liveDemo } = this.props;
    const parentWidth = $(window.parent.document).width();
    const hiddenPopup = !liveDemo && parentWidth < 760;

    return (
      <div
        className={classNames('dpdesignportal-state-buttons', {
          'dpdesignportal-agent-message': !small,
          'dpdesignportal-online-agents': small,
          'hidden-popup':                 hiddenPopup
        })}
      >
        <div className={classNames('preemtive-chat', { small, 'position-left': widgetPosition === 'bottom.left' })}>
          <a href="#" className="close-panel" onClick={this.onClose}>
            <i className="fa fa-times" />
          </a>
          {children}
        </div>
      </div>
    );
  }
}
