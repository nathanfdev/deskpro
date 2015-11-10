import React, { Component, PropTypes } from 'react';

export class Button extends Component {
  static propTypes = {
    title: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    icon: PropTypes.string.isRequired
  };

  render() {
    const { title, label, icon } = this.props;
    return (
      <a href="#" className="dpwd-navigation-dropdown-top-row-button">
        <span
          className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
          {title}
        </span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className={'fa ' + icon}></i></span>
        <span className="dpwd-navigation-dropdown-top-row-button-text">{label}</span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className="fa fa-caret-down"></i></span>
      </a>
    );
  }
}

