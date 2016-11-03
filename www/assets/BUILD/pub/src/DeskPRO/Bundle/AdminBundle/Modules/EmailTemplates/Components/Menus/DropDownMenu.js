import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';

class DropDownMenu extends React.Component {
  static propTypes    = {
    label:     PropTypes.string,
    icon:      PropTypes.string,
    onClick:   PropTypes.func,
    className: PropTypes.string,
    children:  PropTypes.node
  };
  static defaultProps = {
    onClick() {},
  };

  openMenu = () => {
    this.dropdown.openPopup();
    if (this.props.onClick) {
      this.props.onClick();
    }
  };

  closeMenu = () => {
    this.dropdown.closePopup();
  };

  render() {
    return (
      <PopUp
        positionMy="left top-1px"
        positionAt="left bottom"
        zIndex={100}
        content={this.props.children}
        ref={(c) => { this.dropdown = c; }}
        className="email-dropdown-menu"
        autoOpen={false}
      >
        <div
          className={classNames(this.props.className)}
          onClick={this.openMenu}
        >
          <i className={classNames('icon', this.props.icon)} />
          {this.props.label}
          <i className="fa fa-caret-down" />
        </div>
      </PopUp>
    );
  }
}
export default DropDownMenu;
