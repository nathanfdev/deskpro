import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class RadioOption extends React.Component {

  static propTypes = {
    onClick:  PropTypes.func.isRequired,
    param:    PropTypes.string.isRequired,
    isActive: PropTypes.bool,
    label:    PropTypes.any.isRequired,
    value:    PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    children: PropTypes.any
  };

  render() {
    const { label, isActive, param, value, onClick, children } = this.props;

    return (
      <li onClick={() => onClick(param, value, isActive)}>
        <div className="dpw--popup-item-box">
        <span className={classNames('dpwd-radio-button', { active: isActive })}>
          <span className="dpwd-radio-button-disc" />
          <span className="radio-button-title">{label}</span>
        </span>
        </div>
        {children}
      </li>
    );
  }
}
