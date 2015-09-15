import React, {Component} from 'react';
import classNames from 'classnames';

export class DropdownMenu extends Component {

  render() {
    return (
      <div className="dpw-navigation-dropdown">
        <ul>
          {this.props.children}
        </ul>
      </div>
    );
  }

}

export class Option extends Component {

  render() {
    const {active, option, onClick } = this.props;
    var classes = classNames('dpw-navigation-dropdown-item', {
      'active': active
    });
    var icons = classNames('fa', option.icon);
    var click   = active ?
                  (e)=> {
                    e.preventDefault()
                  }
      : onClick;
    return (
      <li>
        <a href="#" className={classes} onClick={click.bind(this)}>
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-icon dpw-navigation-dropdown-item-icon-2x"><i
                  className={icons}></i></span>
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