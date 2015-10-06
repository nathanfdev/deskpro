import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export class Option extends Component {

  static propTypes = {
    option: PropTypes.object.isRequired,
    active: PropTypes.bool.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    callback: PropTypes.func
  };

  handleClick(option, event) {
    event.preventDefault();
    const {callback, toggleDropdown} = this.props;
    toggleDropdown();
    if (callback) {
      callback(option);
    }
  }

  render() {
    const {active, option } = this.props;
    var classes = classNames('dpw-navigation-dropdown-item', {
      'active': active
    });
    var icons = classNames('fa', option.icon);
    var click = active ?
      (event)=> event.preventDefault()
      : this.handleClick.bind(this, option);
    return (
      <li>
        <a href="#" className={classes} onClick={click}>
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-icon dpw-navigation-dropdown-item-icon-2x">
                  <i className={icons}></i>
                </span>
              </span>
          <span className="dpw-navigation-dropdown-item-title">{option.label}</span>
          {active ?
            <span className="dpw-navigation-dropdown-item-status"><i className="fa fa-check"></i></span>
            : ''
          }
        </a>
      </li>
    );
  }
}

export class DropdownMenuFooter extends Component {

  render() {
    return (
      <li>
        <div className="dpw-navigation-dropdown-item dpw-navigation-dropdown-footer">
          {this.props.children}
        </div>
      </li>
    );
  }
}