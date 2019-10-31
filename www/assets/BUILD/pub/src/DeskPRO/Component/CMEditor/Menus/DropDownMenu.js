import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { PopUp } from '../../Semantic/PopUp';

class DropDownMenu extends React.Component {
  static propTypes    = {
    label:      PropTypes.string,
    icon:       PropTypes.string,
    onClick:    PropTypes.func,
    className:  PropTypes.string,
    children:   PropTypes.node,
    disabled:   PropTypes.bool,
    positionMy: PropTypes.string,
    positionAt: PropTypes.string,
  };
  static defaultProps = {
    onClick() {},
    disabled:   false,
    positionMy: 'left top-1px',
    positionAt: 'left bottom',
  };

  constructor(props) {
    super(props);
    this.id = '';
  }

  openMenu = () => {
    if (this.props.disabled === false) {
      this.dropdown.openPopup();
      if (this.props.onClick) {
        this.props.onClick();
      }
    }
  };

  closeMenu = () => {
    this.dropdown.closePopup();
  };

  render() {
    const { positionMy, positionAt } = this.props;
    return (
      <PopUp
        positionMy={positionMy}
        positionAt={positionAt}
        zIndex={100}
        content={this.props.children}
        ref={(c) => { this.dropdown = c; }}
        className="editor-dropdown-menu"
        autoOpen={false}
        manual
      >
        <div
          className={classNames(this.props.className)}
          onClick={this.openMenu}
        >
          <i className={classNames('icon', this.props.icon)} />
          {this.props.label}
          <i className="fas fa-caret-down" />
        </div>
      </PopUp>
    );
  }
}
export default DropDownMenu;
