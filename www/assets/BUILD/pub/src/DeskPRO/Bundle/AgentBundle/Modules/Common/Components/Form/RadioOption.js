import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

import { connect } from 'react-redux';
@connect()
export class RadioOption extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    param: PropTypes.string.isRequired,
    isActive: PropTypes.bool,
    label: PropTypes.string.isRequired,
    value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func,
    children: PropTypes.any
  };

  handleClick(event) {
    event.stopPropagation();
    const { param, value, setParams, dispatch, isActive, resetSingleAction } = this.props;
    if (isActive) {
      dispatch(resetSingleAction(param));
    } else {
      dispatch(setParams({ [param]: value }));
    }
  }

  render() {
    const {label, isActive} = this.props;
    const classes = classNames('dpwd-radio-button', { 'active': isActive });

    return (
      <li onClick={this.handleClick.bind(this)}>
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
