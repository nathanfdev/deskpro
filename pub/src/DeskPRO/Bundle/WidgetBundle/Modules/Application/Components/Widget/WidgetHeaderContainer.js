import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { closeWidget } from '../../Actions/dpWindowActions';

@connect()
export class WidgetHeaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    title: PropTypes.string
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
          <span className="dpdesignportal-logo sample-logo" />
          <h1>{this.props.title}</h1>
        </div>
      </div>
    );
  }
}
