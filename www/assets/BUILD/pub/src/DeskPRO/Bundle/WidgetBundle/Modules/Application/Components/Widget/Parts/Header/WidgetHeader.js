import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { isEndedSelector } from '../../../../../Chat/Selectors/chat';

@connect(state => ({
  chatEnded: isEndedSelector(state)
}))
export class WidgetHeader extends React.Component {

  static propTypes = {
    onOpenMenu:  PropTypes.func,
    onClose:     PropTypes.func,
    companyName: PropTypes.string,
    companyLogo: PropTypes.string,
    chatEnded:   PropTypes.bool
  };

  onOpenMenu = (event) => {
    event.preventDefault();
    this.props.onOpenMenu();
  };

  onClose = (event) => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    const { companyName, companyLogo, chatEnded } = this.props;

    let minimizeButton;
    if (chatEnded) {
      minimizeButton = <i className="fa fa-times" />;
    } else {
      minimizeButton = <i className="fa fa-minus" />;
    }

    return (
      <div className="dpdesignportal-header">
        {false /* disabled for now */ &&
          <a href="#" className="dpdesignportal-header-controls left" onClick={this.onOpenMenu}>
            <i className="fas fa-bars" />
          </a>
        }

        <a
          href="#"
          className="dpdesignportal-header-controls dpdesignportal-mobile-nav-control right"
          onClick={this.onClose}
        >
          <span className="dpdesignportal-control-hide">
            {minimizeButton}
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
