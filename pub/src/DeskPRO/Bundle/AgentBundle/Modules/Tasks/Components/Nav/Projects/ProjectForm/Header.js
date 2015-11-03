import React, { PropTypes } from 'react';

export class Header extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpw--popup-header">
        <i className="fa fa-tags"/> {this.props.children}
      </div>
    );
  }
}
