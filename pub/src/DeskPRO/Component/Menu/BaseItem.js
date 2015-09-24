import React from 'react';
import Menu from 'DeskPRO/Component/Menu/Menu';
import ItemFormat from 'DeskPRO/Component/Menu/ItemFormat';
import Positioned from 'DeskPRO/Component/Positioned';

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
    format: React.PropTypes.string
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
      openMenu: false
    };
  },

  /**
   * Open the menu
   * @return {void}
   */
  openMenu: function() {
    this.setState({
      openMenu: true
    });
  },

  /**
   * Close the menu
   * @return {void}
   */
  closeMenu: function() {
    this.setState({
      openMenu: false
    });
  },

  /**
   * Handle clicks outside the item
   * @return {void}
   */
  handleClickOutside: function() {
    this.closeMenu();
  },

  /**
   * Toggle the menu open/closed
   * @return {void}
   */
  toggleMenu: function() {
    this.setState({
      openMenu: !this.state.openMenu
    });
  },

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render: function() {
    const baseClass = 'dpw-navigation-dropdown-item';

    const onClickAction = this.props.onClick ? this.props.onClick : () => {};
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

      // Calculate the menu
      const menu = React.Children.map(children, (child) => {
        if (child.type && child.type.displayName === 'Menu') {
          keepMenuState = true;
          const parentLevel = this.props.parentMenuLevel ? this.props.parentMenuLevel : 1;
          const childProps = child.props;
          childProps.menuLevel = parentLevel + 1;

          return (<Positioned isOpen={this.state && this.state.openMenu}
                              positionMy="left top"
                              positionAt="right top"
                              collision="none"
                              positionTarget={this}
                              key={child}>
              <Menu {...childProps}
                    isOpen={this.state && this.state.openMenu} />
          </Positioned>);
        }
      });

      const divClasses = [baseClass];

      if (this.props.widgetClass) {
        divClasses.push(this.getWidgetClass(this.props.widgetClass));
      }

      if (this.props.disabled) {
        divClasses.push('disabled');
      }

      // Only add an active state if the menu is open and exists
      if (this.state && this.state.openMenu && keepMenuState) {
        divClasses.push('active');
      }

      let divClass = divClasses.join(' ');

      if (this.props.overrideWidgetClass) {
        divClass = this.getWidgetClass(this.props.widgetClass);
      }

      if (contents) {
        output.push(<a className={divClass} href="#" onClick={onClickAction} onMouseOver={onMouseOverAction} onMouseOut={onMouseOutAction}>
          {this.formatOutput(contents)}
        </a>);
      }

      const itemList = React.Children.map(children, (child) => {
        if (child.type && child.type.displayName === 'ItemList') {
          return child;
        }
      });

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
  },

  getWidgetClass: function(divClass) {
    return divClass;
  },

  formatOutput: function(output) {
    if (this.props.format && this.props.format === 'item') {
      return (<ItemFormat {...this.props}>{output}</ItemFormat>);
    }

    return output;
  }
});

module.exports = BaseItem;
