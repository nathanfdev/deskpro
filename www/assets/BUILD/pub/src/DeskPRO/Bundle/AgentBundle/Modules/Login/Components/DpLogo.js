import PropTypes from 'prop-types';
import React from 'react';

export class DpLogo extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <div className="deskpro-loading-welcome-back">
        <div className="deskpro-loading-logo">
          <a href="https://www.deskpro.com/" className="logo"></a>
        </div>

        {this.props.children}
      </div>
    );
  }
}
