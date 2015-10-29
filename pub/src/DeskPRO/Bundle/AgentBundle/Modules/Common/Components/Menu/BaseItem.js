import React from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import ItemFormat from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemFormat';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import classNames from 'classnames';
import jQuery from 'jquery';

const BaseItem = React.createClass({

  /**
   * Valid prop types
   * @type {Object}
   */
  propTypes: {
    label: React.PropTypes.string,
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
  mixins: [require('react-onclickoutside')],

  getInitialState() {
    return {
      openMenu: false,
      openInnerList: false
    };
  },

  /**
   * Execute an action on click
   * @param {object} event Click event
   * @return {void}
   */
  onClickAction: function(event) {
    event.preventDefault();

    if (this.props.onClick) {
      this.props.onClick();
    }

    if (!this.props.keepOpen && this.props.closeMenu) {
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
    this.setState({
      openMenu: true
    });
  },

  /**
   * Close the menu
   * @return {void}
   */
  closeMenu: function() {
    if (this.props.setActiveItem) {
      this.props.setActiveItem({});
    }
    this.setState({
      openMenu: false
    });
  },

  /**
   * Handle clicks outside the item
   * @param {Event} event Click event
   * @return {void}
   */
  handleClickOutside: function(event) {
    // Don't handle clicks for menu items - they deal with that themselves
    const closestItem = jQuery(event.target).parents('.dropdown-nav-item');
    const closestDateTimePicker = jQuery(event.target).closest('.dpw-date-picker');
    if (closestItem.length === 0 && !closestDateTimePicker) {
      this.closeMenu();
    }
  },

  /**
   * @TODO
   * Toggle the menu open/closed
   * @return {void}
   */
  toggleMenu: function() {
    if (!this.state.openMenu) {
      this.openMenu();
    } else {
      this.closeMenu();
    }
  },

  /**
   * Toggle the inner list
   * @param  {object} event The click event
   * @return {[type]} [description]
   */
  toggleInnerList: function(event) {
    event.preventDefault();
    this.setState({
      openInnerList: !this.state.openInnerList
    });
  },

  /**
   * Format the output according to the format prop
   * @param {mixed} output        Output before formatting
   * @param {boolean} hasMenu     If nested Menu exists
   * @param {boolean} hasItemList If nested ItemList exists
   * @return {mixed} output       The formatted output
   */
  formatOutput: function(output, hasMenu = false, hasItemList = false) {
    if (this.props.format) {
      if (this.props.format === 'item') {
        return (
          <ItemFormat {...this.props} hasMenu={hasMenu} hasItemList={hasItemList}
                                      toggleInnerList={this.toggleInnerList}/>
        );
      } else if (this.props.format === 'filter') {
        return (
          <ItemFormat {...this.props}/>
        );
      }
    }
    return output;
  },

  checkIfMenuExists: function() {
    let hasMenu = false;
    if (this.props.children) {
      React.Children.map(this.props.children,
        (child) => {
          if (child && child.type && child.type.displayName === 'Menu') {
            hasMenu = true;
          }
        });
    }
    return hasMenu;
  },

  checkIfItemListExists() {
    let hasItemList = false;
    if (this.props.children) {
      React.Children.map(this.props.children,
        (child) => {
          if (child && child.type && child.type.displayName === 'ItemList') {
            hasItemList = true;
          }
        });
    }
    return hasItemList;
  },

  renderLabel(hasMenu, hasItemList) {
    const {label} = this.props;
    if (label) {
      const onMouseOverAction = this.props.onMouseOver ? this.props.onMouseOver : () => {
      };
      const onMouseOutAction = this.props.onMouseOut ? this.props.onMouseOut : () => {
      };

      const divClasses = classNames(this.props.widgetClass, {
        'dpw-navigation-dropdown-item dropdown-nav-item': !this.props.overrideWidgetClass,
        'dpw-navigation-dropdown-item-disabled': this.props.disabled,
        'dpw-navigation-dropdown-item-condensed': this.props.condensed,
        'active': this.props.activeItem === this && hasMenu || this.props.isActive
      });

      return (
        <div className={this.props.widgetClass}>
          <a href="#" className={divClasses}
             onClick={this.onClickAction} onMouseOver={onMouseOverAction} onMouseOut={onMouseOutAction}>
            {this.formatOutput(label, hasMenu, hasItemList)}
          </a>
        </div>
      );
    }
  },

  renderMenu(hasMenu) {
    if (hasMenu) {
      return React.Children.map(this.props.children, (child) => {
        if (child && child.type && child.type.displayName === 'Menu') {
          const parentLevel = this.props.parentMenuLevel ? this.props.parentMenuLevel : 1;
          const childProps = child.props;
          const menuLevel = parentLevel + 1;

          return (<Positioned isOpen
                              positionMy="left top"
                              positionAt="right top"
                              collision="none"
                              positionTarget={this}
                              key={child}>
            <Menu {...childProps} menuLevel={menuLevel}
                                  isOpen={this.props.activeItem === this} closeMenu={this.closeMenu}/>
          </Positioned>);
        }
      });
    }
  },

  renderItemList(hasItemList) {
    if (hasItemList) {
      return React.Children.map(this.props.children, (child) => {
        if (child && child.type && child.type.displayName === 'ItemList' && this.state.openInnerList) {
          return child;
        }
      });
    }
  },

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render: function() {
    const hasItemList = this.checkIfItemListExists();
    const hasMenu = this.checkIfMenuExists();

    let childrenOutput = [];
    childrenOutput = childrenOutput.concat(this.renderMenu(hasMenu));
    childrenOutput = childrenOutput.concat(this.renderItemList(hasItemList));
    return (
      <div onMouseOver={this.props.subMenuMode ? ()=>{} : this.openMenu}
           onClick={this.props.subMenuMode && this.props.subMenuMode === 'click' ? this.toggleMenu : ()=>{}}>
        {this.renderLabel(hasMenu, hasItemList)}
        {childrenOutput}
      </div>
    );
  }
});

module.exports = BaseItem;
