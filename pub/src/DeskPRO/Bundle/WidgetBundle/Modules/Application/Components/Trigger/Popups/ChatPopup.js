import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class ChatPopup extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    small: PropTypes.bool,
    widgetPosition: PropTypes.string,
    children: PropTypes.any
  };

  render() {
    const { small, widgetPosition, backgroundColor, children } = this.props;

    return (
      <div className={classNames('dpdesignportal-state-buttons', {
        'dpdesignportal-agent-message': !small,
        'dpdesignportal-online-agents': small
      })}>
        <div
          className={classNames('preemtive-chat', {
            'small': small,
            'position-left': widgetPosition === 'bottom.left'
          })}
          style={{
            borderColor: backgroundColor
          }}>
          {children}
          <div
            className="pointer"
            style={{
              borderTopColor: backgroundColor
            }} />
        </div>
      </div>
    );
  }
}
