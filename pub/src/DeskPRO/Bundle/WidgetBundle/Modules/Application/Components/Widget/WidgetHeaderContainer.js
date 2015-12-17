import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { closeWidget } from '../../Actions/dpWindowActions';
import { companyNameSelector, companyLogoSelector } from '../../Selectors/dpWindow';

@connect(state => ({
  companyName: companyNameSelector(state),
  companyLogo: companyLogoSelector(state)
}))
export class WidgetHeaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    companyName: PropTypes.string,
    companyLogo: PropTypes.string
  };

  onOpenMenu = event => {
    event.preventDefault();
    console.log('onOpenMenu');
  };

  onClose = event => {
    event.preventDefault();
    this.props.dispatch(closeWidget());
  };

  render() {
    const { companyName, companyLogo } = this.props;

    return (
      <div className="dpdesignportal-header">
        {false /* disabled for now */ &&
          <a href="#" className="dpdesignportal-header-controls left" onClick={this.onOpenMenu}>
            <i className="fa fa-navicon"></i>
          </a>
        }

        <a href="#" className="dpdesignportal-header-controls dpdesignportal-mobile-nav-control right" onClick={this.onClose}>
          <span className="dpdesignportal-control-hide"><i className="fa fa-times"></i></span>
        </a>

        <div className="dpdesignportal-header-mark">
          <img src={companyLogo} className="dpdesignportal-logo sample-logo" />
          <h1>{companyName}</h1>
        </div>
      </div>
    );
  }
}
