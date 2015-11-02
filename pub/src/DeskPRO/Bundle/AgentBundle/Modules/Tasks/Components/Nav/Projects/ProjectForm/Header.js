import React, { PropTypes } from 'react';

export class Header extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    const { children } = this.props;

    return (
      <div className="dpw--popup-header">
        <i className="fa fa-tags"/> {children}
      </div>
    );
  }
}
