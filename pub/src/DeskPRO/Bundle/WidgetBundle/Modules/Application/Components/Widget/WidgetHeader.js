import React, { PropTypes } from 'react';

export class WidgetHeader extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    onClose: PropTypes.func.isRequired
  };

  onOpenMenu = event => {
    event.preventDefault();
    console.log('onOpenMenu');
  };

  onClose = event => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    return (
      <div className="dpdesignportal-header">
        <a href="#" className="dpdesignportal-header-controls left" onClick={this.onOpenMenu}>
          <i className="fa fa-navicon"></i>
        </a>

        <a href="#" className="dpdesignportal-header-controls dpdesignportal-mobile-nav-control right" onClick={this.onClose}>
          <span className="dpdesignportal-control-hide"><i className="fa fa-times"></i></span>
        </a>

        <div className="dpdesignportal-header-mark">
          <span className="dpdesignportal-logo sample-logo" />
          <h1>{this.props.title}</h1>
        </div>
      </div>
    );
  }
}
