import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';

export class RadioOption extends Component {

  static propTypes = {
    onClick:  PropTypes.func.isRequired,
    param:    PropTypes.string.isRequired,
    isActive: PropTypes.bool,
    label:    PropTypes.any.isRequired,
    value:    PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    children: PropTypes.any
  };

  handleClick = () => {
    const { isActive, param, value, onClick } = this.props;
    onClick(param, value, isActive);
  };

  render() {
    const { label, isActive } = this.props;
    const classes = classNames('dpwd-radio-button', { active: isActive });

    return (
      <li onClick={this.handleClick}>
        <div className="dpw--popup-item-box">
        <span className={classes}>
          <span className="dpwd-radio-button-disc" />
          <span className="radio-button-title">{label}</span>
        </span>
        </div>
        {this.props.children}
      </li>
    );
  }
}
