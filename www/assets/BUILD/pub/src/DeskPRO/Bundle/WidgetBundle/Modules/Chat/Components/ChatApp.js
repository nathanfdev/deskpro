import PropTypes from 'prop-types';
import React from 'react';

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
