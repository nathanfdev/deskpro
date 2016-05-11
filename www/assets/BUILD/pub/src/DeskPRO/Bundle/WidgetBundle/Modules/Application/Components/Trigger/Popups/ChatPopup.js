import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class ChatPopup extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    small:           PropTypes.bool,
    widgetPosition:  PropTypes.string,
    children:        PropTypes.any,
    liveDemo:        PropTypes.bool
  };

  render() {
    const { small, widgetPosition, backgroundColor, children, liveDemo } = this.props;
    const parentWidth = $(window.parent.document).width();
    const hiddenPopup = !liveDemo && parentWidth < 760;

    return (
      <div className={classNames('dpdesignportal-state-buttons', {
        'dpdesignportal-agent-message': !small,
        'dpdesignportal-online-agents': small,
        'hidden-popup': hiddenPopup
      })}>
        <div
          className={classNames('preemtive-chat', { small, 'position-left': widgetPosition === 'bottom.left' })}
          style={{ borderColor: backgroundColor }}>

          {children}
          <div className="pointer" style={{ borderTopColor: backgroundColor }}></div>
        </div>
      </div>
    );
  }
}
