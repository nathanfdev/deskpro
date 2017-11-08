import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';

export class Button extends Component {
  static propTypes = {
    title:    PropTypes.string.isRequired,
    label:    PropTypes.string.isRequired,
    icon:     PropTypes.string,
    isActive: PropTypes.bool,
    onClick:  PropTypes.func.isRequired
  };

  componentWillMount() {
    this.setState({
      isActive: this.props.isActive
    });
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      isActive: nextProps.isActive
    });
  }

  onClick = event => {
    event.preventDefault();
    if (this.props.onClick) {
      this.props.onClick();
    }
  };

  render() {
    const { title, label, icon } = this.props;
    var classes = classNames('dpwd-navigation-dropdown-top-row-button', { 'active': this.state.isActive });

    return (
      <a href="#" className={classes} onClick={this.onClick}>
        <span
          className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey"
        >
          {title}
        </span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className={'fa ' + icon}></i></span>
        <span className="dpwd-navigation-dropdown-top-row-button-text">{label}</span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className="fa fa-caret-down"></i></span>
      </a>
    );
  }
}

