import React from 'react';
import $ from "jquery";
import classNames from 'classnames';

export class ControlBar extends React.Component {
  render() {
    return (
      <div className="dpwd-navigation-dropdown-top-row">
        {this.props.children}
      </div>
    );
  }
}

export class ControlButtonsRow extends React.Component {
  render() {
    return (
      <ul className="dpwd-navigation-dropdown-top-row-main-list">
        {this.props.children}
      </ul>
    );
  }
}

export class ControlButton extends React.Component {
  render() {
    const { title, label, icon } = this.props;
    var classes = classNames('fa', icon);
    return (
      <a href="#" className="dpwd-navigation-dropdown-top-row-button" onClick={this.toggleDropdown.bind(this)}>
        <span
          className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">{title}</span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className={classes}></i></span>
        <span className="dpwd-navigation-dropdown-top-row-button-text">{label}</span>
        <span className="dpwd-navigation-dropdown-top-row-button-icon"><i className="fa fa-caret-down"></i></span>
      </a>
    );
  }

  toggleDropdown(event) {
    event.preventDefault();
    const {dropdownClass} = this.props;
    let elem             = $(event.target),
          buttonPosition = elem.closest('a').offset(),
          dropdown       = $(event.target).closest('.dpwd-navigation-dropdown-top-row').find('.' + dropdownClass);
    dropdown.css('left', buttonPosition.left).css('top', buttonPosition.bottom).css('width', '200px').css('position', 'fixed').css('z-index', 200).toggle();
  }
}
