import React, { PropTypes } from 'react';

export class Popup extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <div className="sidebar-hover">
        <div className="dpw--popup-main">
        {this.props.children}
        </div>
      </div>
    );
  }
}
