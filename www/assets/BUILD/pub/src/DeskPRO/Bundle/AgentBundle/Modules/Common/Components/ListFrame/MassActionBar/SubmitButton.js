import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';

export class SubmitButton extends Component {
  static propTypes = {
    label:    PropTypes.string.isRequired,
    onClick:  PropTypes.func.isRequired,
    isActive: PropTypes.bool
  };

  clickHandler = (event) => {
    event.preventDefault();
    const { onClick } = this.props;
    onClick();
  };

  render() {
    const { label, isActive } = this.props;
    const classes = classNames('top-row-action-button-link', { 'has-value': isActive });

    return (
      <li>
        <span
          className="dpwd-navigation-dropdown-top-row-action-button dpwd-navigation-dropdown-top-row-action-button-flat"
        >
          <a href="" className={classes} onClick={this.clickHandler}>
            <span
              className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey"
            >
              {label}
            </span>
          </a>
        </span>
      </li>
    );
  }
}
