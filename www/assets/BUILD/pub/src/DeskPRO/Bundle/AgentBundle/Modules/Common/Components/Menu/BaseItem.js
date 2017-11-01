import PropTypes from 'prop-types';
import React, { Component } from 'react';
import createFragment from 'react-addons-create-fragment';
import classNames from 'classnames';
import { Menu } from './Menu';
import { ItemFormat } from './ItemFormat';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class BaseItem extends Component {

  static propTypes = {
    label:               PropTypes.string,
    widgetClass:         PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children:            PropTypes.node,
    parsable:            PropTypes.node,
    onClick:             PropTypes.func,
    onMouseOver:         PropTypes.func,
    onMouseOut:          PropTypes.func,
    subMenuMode:         PropTypes.string,
    parentMenuLevel:     PropTypes.number,
    disabled:            PropTypes.bool,
    condensed:           PropTypes.bool,
    format:              PropTypes.string,
    keepOpen:            PropTypes.bool,
    hasMenu:             PropTypes.bool,
    hasItemList:         PropTypes.bool,
    activeItem:          PropTypes.object,
    setActiveItem:       PropTypes.func,
    closeMenu:           PropTypes.func,
    isActive:            PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      openMenu:      false,
      openInnerList: false
    };
  }

  /**
   * Execute an action on click
   * @param {object} event Click event
   * @return {void}
   */
  onClickAction = (event) => {
    event.preventDefault();
    if (this.props.onClick) {
      this.props.onClick();
    }
    if (!this.props.keepOpen && this.props.closeMenu) {
      this.props.closeMenu();
    }
  };

  /**
   * Open the menu
   * @return {void}
   */
  openMenu = () => {
    this.setState({ openMenu: true });
  };

  /**
   * Close the menu
   * @return {void}
   */
  closeMenu = () => {
    if (this.updater.length > 0) {
      this.setState({ openMenu: false });
    }
  };

  /**
   * Toggle the menu open/closed
   * @return {void}
   */
  toggleMenu = () => {
    this.setState({ openMenu: !this.state.openMenu });
  };

  /**
   * Toggle the inner list
   * @param  {object} event The click event
   * @return {[type]} [description]
   */
  toggleInnerList = (event) => {
    event.preventDefault();
    this.setState({ openInnerList: !this.state.openInnerList });
  };

  /**
   * Format the output according to the format prop
   * @param {mixed} output        Output before formatting
   * @param {boolean} hasMenu     If nested Menu exists
   * @param {boolean} hasItemList If nested ItemList exists
   * @return {mixed} output       The formatted output
   */
  formatOutput(output, hasMenu = false, hasItemList = false) {
    const { format } = this.props;
    if (format) {
      if (format === 'item') {
        return (
          <ItemFormat {...this.props}
            hasMenu={hasMenu}
            hasItemList={hasItemList}
            toggleInnerList={this.toggleInnerList}
          />
        );
      } else if (format === 'filter') {
        return (
          <ItemFormat {...this.props} />
        );
      }
    }
    return output;
  }

  renderLabel(hasMenu, hasItemList) {
    const { label, overrideWidgetClass, disabled, activeItem, isActive, condensed } = this.props;
    const { onMouseOver, onMouseOut } = this.props;
    if (label) {
      const onMouseOverAction = onMouseOver ? onMouseOver : () => {
      };
      const onMouseOutAction  = onMouseOut ? onMouseOut : () => {
      };

      const divClasses = classNames(this.props.widgetClass, {
        'dpw-navigation-dropdown-item dropdown-nav-item': !overrideWidgetClass,
        'dpw-navigation-dropdown-item-disabled':          disabled,
        'dpw-navigation-dropdown-item-condensed':         condensed,
        active:                                           activeItem === this && hasMenu || isActive
      });

      return (
        <a
          href="#"
          className={divClasses}
          onClick={this.onClickAction}
          onMouseOver={onMouseOverAction}
          onMouseOut={onMouseOutAction}
        >
          {this.formatOutput(label, hasMenu, hasItemList)}
        </a>
      );
    }
    return null;
  }

  renderMenu() {
    if (this.props.hasMenu) {
      return React.Children.map(this.props.children, child => {
        const isOpen = this.state.openMenu;
        if (child && child.type && child.type.displayName === 'Menu') {
          const parentLevel = this.props.parentMenuLevel ? this.props.parentMenuLevel : 1;
          const childProps  = child.props;
          const menuLevel   = parentLevel + 1;
          return (
            <Detached
              isOpen
              positionMy="left top"
              positionAt="right top"
              collision="none"
              positionTarget={this}
              key={child}
            >
              <ClickOut
                onClickOut={this.closeMenu}
                ignoreNodes={[this.refs.item]}
              >
                <Menu {...childProps}
                  menuLevel={menuLevel}
                  isOpen={isOpen}
                />
              </ClickOut>
            </Detached>
          );
        }
        return null;
      });
    }
    return null;
  }

  renderItemList(expanded) {
    if (this.props.hasItemList) {
      return React.Children.map(this.props.children, child => {
        if (child && child.type && child.type.displayName === 'ItemList' && expanded) {
          return child;
        }
        return null;
      });
    }
    return null;
  }

  render() {
    const { hasMenu, hasItemList } = this.props;
    const childrenOutput = { menu: this.renderMenu(), itemList: this.renderItemList(this.state.openInnerList) };

    return (
      <li
        ref="item"
        onMouseOver={this.props.subMenuMode ? () => {} : this.openMenu}
        onClick={this.props.subMenuMode && this.props.subMenuMode === 'click' ? this.toggleMenu : () => {}}
      >
        {this.renderLabel(hasMenu, hasItemList)}
        {createFragment(childrenOutput)}
      </li>
    );
  }
}
