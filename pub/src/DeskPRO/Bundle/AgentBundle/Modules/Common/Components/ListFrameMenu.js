import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export class ListFrameMenu extends React.Component {

  static propTypes = {
    children: PropTypes.any.isRequired
  };

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

  render() {
    const {toggleDropdown, title, label, icon } = this.props;
    const classes = classNames('fa', icon);
    const onClick = (e) => {
      e.preventDefault();
      if (toggleDropdown) toggleDropdown(e);
    };

    return (
      <a href="#" className="dpwd-navigation-dropdown-top-row-button" onClick={onClick}>
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

  static propTypes = {
    onClick: PropTypes.func.isRequired,
    massAction: PropTypes.bool.isRequired,
    count: PropTypes.string
  };

  renderCount(count) {
    if (count) {
      return (
        <div className="dpwd-navigation-top-row-mass-action-checkbox-count">
          <span>{count}</span>
        </div>
      );
    }
  }

  render() {
    const {count, massAction, onClick} = this.props;

    var divClasses = classNames('dpwd-navigation-top-row-mass-action-checkbox', {'active': massAction === true});
    var checkboxClasses = classNames('fa', {'fa-check': massAction === true});

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container">
        <div className={divClasses} onClick={onClick}>
          <i className={checkboxClasses}></i>
        </div>
        {this.renderCount(count)}
      </div>
    );
  }

}
