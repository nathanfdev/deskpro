import React from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import ItemFormat from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemFormat';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import classNames from 'classnames';
import jQuery from 'jquery';

const BaseItem = React.createClass({

  /**
   * Valid prop types
   * @type {Object}
   */
  propTypes: {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node,
    parsable: React.PropTypes.node,
    onClick: React.PropTypes.func,
    onMouseOver: React.PropTypes.func,
    onMouseOut: React.PropTypes.func,
    subMenuMode: React.PropTypes.string,
    parentMenuLevel: React.PropTypes.number,
    disabled: React.PropTypes.bool,
    condensed: React.PropTypes.bool,
    format: React.PropTypes.string,
    keepOpen: React.PropTypes.bool,
    activeItem: React.PropTypes.object,
    setActiveItem: React.PropTypes.func,
    closeMenu: React.PropTypes.func,
    isActive: React.PropTypes.bool
  },

  /**
   * Mixins
   * @type {Array}
   */
  mixins: [
    require('react-onclickoutside')
  ],

  /**
   * Constructor
   * @param  {Object} props The component props
   * @return {void}
   */
  getInitialState: function() {
    return {
      openMenu: false,
      openInnerList: false
    };
  },

  /**
   * Execute an action on click
   * @return {void}
   */
  onClickAction: function() {
    if (this.props.onClick) {
      this.props.onClick();
    }

    if (!this.props.keepOpen) {
      this.props.closeMenu();
    }
  },

  /**
   * Open the menu
   * @return {void}
   */
  openMenu: function() {
    if (this.props.setActiveItem) {
      this.props.setActiveItem(this);
    }
  },

  /**
   * Close the menu
   * @return {void}
   */
  closeMenu: function() {
    if (this.props.setActiveItem) {
      this.props.setActiveItem({});
    }
  },

  /**
   * Handle clicks outside the item
   * @param {Event} event Click event
   * @return {void}
   */
  handleClickOutside: function(event) {
    // Don't handle clicks for menu items - they deal with that themselves
    const closest = jQuery(event.target).parents('.dpw-navigation-dropdown-item');

    if (closest.length === 0) {
      this.closeMenu();
    }
  },

  /**
   * @TODO
   * Toggle the menu open/closed
   * @return {void}
   */
  toggleMenu: function() {
    this.setState({
      openMenu: !this.state.openMenu
    });
  },

  /**
   * Toggle the inner list
   * @return {[type]} [description]
   */
  toggleInnerList: function() {
    this.setState({
      openInnerList: !this.state.openInnerList
    });
  },

  /*
   * Format the output according to the format prop
   * @param  {mixed} output The output
   * @return {mixed}        The formatted output
   * @param hasMenu
   * @param hasItemList
   */
  formatOutput: function(output, hasMenu = false, hasItemList = false) {
    if (this.props.format && this.props.format === 'item') {
      return (<ItemFormat {...this.props} hasMenu={hasMenu} hasItemList={hasItemList} toggleInnerList={this.toggleInnerList}>{output}</ItemFormat>);
    }

    return output;
  },

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render: function() {
    const baseClass = 'dpw-navigation-dropdown-item';

    const onMouseOverAction = this.props.onMouseOver ? this.props.onMouseOver : () => {};
    const onMouseOutAction = this.props.onMouseOut ? this.props.onMouseOut : () => {};

    let output = [];

    let keepMenuState = false;

    // Loop through the children to work out what to do with menus
    if (this.props.parsable || this.props.children) {
      const children = typeof this.props.children.map === 'function' ? this.props.children : [this.props.children];

      // Calculate the raw menu items
      const contents = React.Children.map(children, (child) => {
        if (!child.type || (child.type.displayName !== 'Menu' && child.type.displayName !== 'ItemList')) {
          return child;
        }
      });

      let hasMenu = false;
      let hasItemList = false;

      // Calculate the menu
      const menu = React.Children.map(children, (child) => {
        if (child.type && child.type.displayName === 'Menu') {
          keepMenuState = true;
          hasMenu = true;
          const parentLevel = this.props.parentMenuLevel ? this.props.parentMenuLevel : 1;
          const childProps = child.props;

          return (<Positioned isOpen
                              positionMy="left top"
                              positionAt="right top"
                              collision="none"
                              positionTarget={this}
                              key={child}>
              <Menu {...childProps} menuLevel={parentLevel + 1}
                    isOpen={this.props.activeItem === this} closeMenu={this.closeMenu} />
          </Positioned>);
        }
      });

      const itemList = React.Children.map(children, (child) => {
        if (child.type && child.type.displayName === 'ItemList') {
          hasItemList = true;
          if (this.state.openInnerList) {
            return child;
          }
        }
      });

      let divClasses = [baseClass];

      if (this.props.widgetClass) {
        divClasses.push(this.props.widgetClass);
      }

      if (this.props.disabled) {
        divClasses.push('dpw-navigation-dropdown-item-disabled');
      }

      // Only add an active state if the menu is open and exists
      if (this.props.activeItem === this && keepMenuState || this.props.isActive) {
        divClasses.push('active');
      }

      if (this.props.condensed) {
        divClasses.push('dpw-navigation-dropdown-item-condensed');
      }

      if (this.props.overrideWidgetClass) {
        divClasses = this.props.widgetClass;
      }

      if (contents) {
        output.push(<a className={classNames(divClasses)} href="#" onClick={this.onClickAction} onMouseOver={onMouseOverAction} onMouseOut={onMouseOutAction}>
          {this.formatOutput(contents, hasMenu, hasItemList)}
        </a>);
      }

      if (itemList) {
        output = output.concat(itemList);
      }

      if (menu) {
        output = output.concat(menu);
      }
    }

    if (this.props.subMenuMode && this.props.subMenuMode === 'click') {
      return (<li onClick={this.toggleMenu}>{output}</li>);
    } else if (this.props.subMenuMode && this.props.subMenuMode === 'none') {
      return (<li>{output}</li>);
    }

    return (<li onMouseOver={this.openMenu}>
      {output}
    </li>);
  }
});

module.exports = BaseItem;
