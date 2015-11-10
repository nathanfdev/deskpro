import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';

export class ViewField extends Component {
  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    fixed: PropTypes.bool,
    isShown: PropTypes.any,
    changeState: PropTypes.func,
    value: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired
  };

  clickHandle(event) {
    event.preventDefault();
    const { isShown, changeState, value } = this.props;
    if (changeState) {
      changeState(value, !isShown);
    }
  }

  renderStatus() {
    const style = {};
    if (!this.props.isShown) {
      style.display = 'none';
    }

    return (
      <span className="dpw-navigation-dropdown-column-list-status" style={style}>
        <i className="fa fa-check"></i>
      </span>
    );
  }

  render() {
    const { label, fixed } = this.props;
    const anchorClasses = classNames('dpw-navigation-dropdown-column-list-item', {
      'dpw-navigation-dropdown-item-disabled': fixed
    });
    const moveIconClass = classNames('fa', {'fa-minus': fixed, 'fa-navicon': !fixed});
    return (
      <li>
        <a className={anchorClasses} href="#" onClick={this.clickHandle.bind(this)}>
          {this.renderStatus()}
          <span className="dpw-navigation-dropdown-column-list-move">
            <i className={moveIconClass}></i>
          </span>
          <span className="dpw-navigation-dropdown-column-list-title">{label}</span>
        </a>
      </li>
    );
  }
}
