import React, { PropTypes } from 'react';

export class ChatApp extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    const { children } = this.props;

    return (
      <div>
        {children}
      </div>
    );
  }
}
