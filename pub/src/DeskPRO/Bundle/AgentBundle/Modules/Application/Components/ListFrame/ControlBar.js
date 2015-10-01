import React, {Component, PropTypes} from 'react';
import $ from 'jquery';
import classNames from 'classnames';

export class ControlBar extends Component {
  render() {
    return (
      <div className="control-bar">
        <div className="ticket-controls-bulk-editing">
          <div className="dpwd-navigation-dropdown-top-row">
            <ul className="dpwd-navigation-dropdown-top-row-main-list">
              {this.props.children}
            </ul>
          </div>
        </div>
      </div>
    );
  }
}

export class ControlButton extends Component {

  static propTypes = {
    toggleDropdown: PropTypes.func.isRequired,
    title: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    icon: PropTypes.string.isRequired
  };

  handleClick(e) {
    e.preventDefault();
    const {toggleDropdown} = this.props;
    const offset = $(e.target).closest('a').position();
    toggleDropdown(offset);
  }

  render() {
    const { title, label, icon } = this.props;
    var classes = classNames('fa', icon);

    return (
      <a href="#" className="dpwd-navigation-dropdown-top-row-button" onClick={this.handleClick.bind(this)}>
        <span
          className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
          {title}
        </span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className={classes}></i></span>
        <span className="dpwd-navigation-dropdown-top-row-button-text">{label}</span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className="fa fa-caret-down"></i></span>
      </a>
    );
  }

}

export class MassActionCheckbox extends Component {

  render() {
    const {count, massAction, onClick} = this.props;

    var divClasses      = classNames('dpwd-navigation-top-row-mass-action-checkbox', {'active': massAction === true});
    var checkboxClasses = classNames('fa', {'fa-check': massAction === true});

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container">
        <div className={divClasses} onClick={onClick}>
          <i className={checkboxClasses}></i>
        </div>

        <div className="dpwd-navigation-top-row-mass-action-checkbox-count">
          <span>{count}</span>
        </div>
      </div>
    );
  }

}