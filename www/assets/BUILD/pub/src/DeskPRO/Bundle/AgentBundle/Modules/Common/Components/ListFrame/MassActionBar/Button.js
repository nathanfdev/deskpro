import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';

export class Button extends Component {
  static propTypes = {
    isActive: PropTypes.bool,
    hasValue: PropTypes.bool,
    label:    PropTypes.string,
    icon:     PropTypes.string,
    onClick:  PropTypes.func.isRequired
  };

  componentWillMount() {
    this.setState({ isActive: this.props.isActive });
  }

  componentWillReceiveProps(nextProps) {
    this.setState({ isActive: nextProps.isActive });
    return nextProps;
  }

  renderContent() {
    const { label, icon } = this.props;
    if (label) {
      return label;
    } else if (icon) {
      const classes = classNames('fa', icon);
      return (<i className={classes} />);
    }

    return null;
  }

  render() {
    const { onClick, hasValue } = this.props;
    const classes = classNames('top-row-action-button-link', { active: this.state.isActive, 'has-value': hasValue });

    return (
      <span className="dpwd-navigation-dropdown-top-row-action-button">
        <a href="" className={classes} onClick={onClick}>
          <span
            className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey"
          >
              {this.renderContent()}
          </span>
          <span className="top-row-action-button-link-extra">
            <span className="dpwd-navigation-dropdown-top-row-button-icon">
              <i className="fa fa-caret-down" />
            </span>
          </span>
        </a>
      </span>
    );
  }
}
