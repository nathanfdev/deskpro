import React, { PropTypes } from 'react';

export class WidgetHeader extends React.Component {

  static propTypes = {
    onOpenMenu:  PropTypes.func,
    onClose:     PropTypes.func,
    companyName: PropTypes.string,
    companyLogo: PropTypes.string
  };

  onOpenMenu = event => {
    event.preventDefault();
    this.props.onOpenMenu();
  };

  onClose = event => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    const { companyName, companyLogo } = this.props;

    return (
      <div className="dpdesignportal-header">
        {false /* disabled for now */ &&
        <a href="#" className="dpdesignportal-header-controls left" onClick={this.onOpenMenu}>
          <i className="fa fa-navicon" />
        </a>
        }

        <a href="#" className="dpdesignportal-header-controls dpdesignportal-mobile-nav-control right"
           onClick={this.onClose}>
          <span className="dpdesignportal-control-hide">
            <i className="fa fa-times" />
          </span>
        </a>

        <div className="dpdesignportal-header-mark">
          {companyLogo && <img src={companyLogo} className="dpdesignportal-logo" />}
          <h1>{companyName}</h1>
        </div>
      </div>
    );
  }
}
