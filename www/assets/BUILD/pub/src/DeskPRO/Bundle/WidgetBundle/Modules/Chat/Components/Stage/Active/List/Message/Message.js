import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class Message extends React.Component {

  static propTypes = {
    isUser:   PropTypes.bool,
    typing:   PropTypes.bool,
    children: PropTypes.any
  };

  render() {
    const { isUser, typing, children } = this.props;

    return (
      <div
        className={classNames('dpdesignportal-message', {
          'agent-message': !isUser,
          'user-message':  isUser,
          'user-typing':   typing
        })}
      >
        {children}
      </div>
    );
  }
}
