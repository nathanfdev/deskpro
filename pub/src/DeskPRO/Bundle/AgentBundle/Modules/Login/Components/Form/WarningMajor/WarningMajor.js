import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class WarningMajor extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  componentDidMount() {
    const $container = jQuery('.dpw-login-warning-major');
    const height = (parseInt($container.css('height').replace(/px/, ''), 10) * -1) + 'px';

    $container.css('top', height);
  }

  render() {
    return (
      <div className="dpw-login-warning-major">
        {this.props.children}
      </div>
    );
  }
}
