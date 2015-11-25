import React, { PropTypes } from 'react';

export class Widget extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="widget-container">
        {this.props.children}
      </div>
    );
  }
}
