import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export class RadioOption extends Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired,
    param: PropTypes.string.isRequired,
    isActive: PropTypes.bool,
    label: PropTypes.string.isRequired,
    value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    children: PropTypes.any
  };

  render() {
    const {label, isActive, param, value, onClick} = this.props;
    const classes = classNames('dpwd-radio-button', { 'active': isActive });

    return (
      <li onClick={onClick.bind(this, param, value, isActive)}>
        <div className="dpw--popup-item-box">
        <span className={classes}>
          <span className="dpwd-radio-button-disc"></span>
          <span className="radio-button-title">{label}</span>
        </span>
        </div>
        {this.props.children}
      </li>
    );
  }
}
