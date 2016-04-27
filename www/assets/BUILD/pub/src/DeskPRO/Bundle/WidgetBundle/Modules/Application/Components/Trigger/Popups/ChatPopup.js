import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class ChatPopup extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    small:           PropTypes.bool,
    widgetPosition:  PropTypes.string,
    children:        PropTypes.any
  };

  render() {
    const { small, widgetPosition, backgroundColor, children } = this.props;
    const parentWidth = $(window.parent.document).width();

    return (
      <div className={classNames('dpdesignportal-state-buttons', {
        'dpdesignportal-agent-message': !small,
        'dpdesignportal-online-agents': small,
        'hidden-popup': parentWidth < 760
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
