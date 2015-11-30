import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class Message extends React.Component {

  static propTypes = {
    type: PropTypes.string,
    typing: PropTypes.bool,
    children: PropTypes.any
  };

  render() {
    const { type, typing, children } = this.props;

    return (
      <div className={classNames(
        'dpdesignportal-message',
        {
          'agent-message': type === 'agent',
          'user-message': type === 'user',
          'user-typing': typing
        })}>

        {children}
      </div>
    );
  }
}
