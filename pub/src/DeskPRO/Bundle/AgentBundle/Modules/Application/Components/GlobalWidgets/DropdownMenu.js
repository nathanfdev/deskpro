import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import $ from "jquery";

export class DropdownMenu extends Component {

  render() {
    const {offset} = this.props;

    return (
      <div className={'dpw-navigation-dropdown'} style={{left:offset.left, top:offset.bottom}}>
        <ul>
          {this.props.children}
        </ul>
      </div>
    );
  }

}

export class Option extends Component {

  static propTypes = {
    option: PropTypes.object.isRequired,
    active: PropTypes.bool.isRequired,
    callback: PropTypes.func.isRequired
  };

  render() {
    const {active, option } = this.props;
    var classes = classNames('dpw-navigation-dropdown-item', {
      'active': active
    });
    var icons   = classNames('fa', option.icon);
    var click   = active ?
                  (e)=> {
                    e.preventDefault()
                  }
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

  handleClick(option, event) {
    event.preventDefault();
    const {callback} = this.props;
    callback(option);
    $(event.target).closest('.dpw-navigation-dropdown').hide();
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