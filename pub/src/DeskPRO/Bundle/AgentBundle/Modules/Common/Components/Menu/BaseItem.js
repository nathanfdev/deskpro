import React, {Component, PropTypes} from 'react';
import createFragment from 'react-addons-create-fragment';
import classNames from 'classnames';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { ItemFormat } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemFormat';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';

export class BaseItem extends Component {

  static propTypes = {
    label: PropTypes.string,
    widgetClass: PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children: PropTypes.node,
    parsable: PropTypes.node,
    onClick: PropTypes.func,
    onMouseOver: PropTypes.func,
    onMouseOut: PropTypes.func,
    subMenuMode: PropTypes.string,
    parentMenuLevel: PropTypes.number,
    disabled: PropTypes.bool,
    condensed: PropTypes.bool,
    format: PropTypes.string,
    keepOpen: PropTypes.bool,
    hasMenu: PropTypes.bool,
    activeItem: PropTypes.object,
    setActiveItem: PropTypes.func,
    closeMenu: PropTypes.func,
    isActive: PropTypes.bool
  };

  componentDidMount() {
    this.state = {
      openMenu: false,
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
    if (this.props.setActiveItem) {
      this.props.setActiveItem(this);
    }
    this.setState({
      openMenu: true
    });
  };

  /**
   * Close the menu
   * @return {void}
   */
  closeMenu = () => {
    if (this.props.setActiveItem) {
      this.props.setActiveItem({});
    }
    this.setState({
      openMenu: false
    });
  };

  /**
   * @TODO
   * Toggle the menu open/closed
   * @return {void}
   */
  toggleMenu = () => {
    if (!this.state.openMenu) {
      this.openMenu();
    } else {
      this.closeMenu();
    }
  };

  /**
   * Toggle the inner list
   * @param  {object} event The click event
   * @return {[type]} [description]
   */
  toggleInnerList = (event) => {
    event.preventDefault();
    this.setState({
      openInnerList: !this.state.openInnerList
    });
  };

  /**
   * Format the output according to the format prop
   * @param {mixed} output        Output before formatting
   * @param {boolean} hasMenu     If nested Menu exists
   * @param {boolean} hasItemList If nested ItemList exists
   * @return {mixed} output       The formatted output
   */
  formatOutput(output, hasMenu = false, hasItemList = false) {
    if (this.props.format) {
      if (this.props.format === 'item') {
        return (
          <ItemFormat {...this.props} hasMenu={hasMenu}
                                      hasItemList={hasItemList}
                                      toggleInnerList={this.toggleInnerList}/>
        );
      } else if (this.props.format === 'filter') {
        return (
          <ItemFormat {...this.props}/>
        );
      }
    }
    return output;
  }

  checkIfMenuExists() {
    let {hasMenu} = this.props;
    if (!hasMenu && this.props.children) {
      React.Children.map(this.props.children,
        (child) => {
          if (child && child.type && child.type.displayName === 'Menu') {
            hasMenu = true;
          }
        });
    }
    return hasMenu;
  }

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
  }

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
        <a href="#" className={divClasses}
           onClick={this.onClickAction} onMouseOver={onMouseOverAction} onMouseOut={onMouseOutAction}>
          {this.formatOutput(label, hasMenu, hasItemList)}
        </a>
      );
    }
  }

  renderMenu(hasMenu) {
    if (hasMenu) {
      return React.Children.map(this.props.children, (child) => {
        if (child && child.type && child.type.displayName === 'Menu') {
          const parentLevel = this.props.parentMenuLevel ? this.props.parentMenuLevel : 1;
          const childProps = child.props;
          const menuLevel = parentLevel + 1;

          return (
            <Detached isOpen
                      positionMy="left top"
                      positionAt="right top"
                      collision="none"
                      positionTarget={this}
                      key={child}>
              <Menu {...childProps} menuLevel={menuLevel}
                                    isOpen={this.props.activeItem === this}
                                    closeMenu={this.closeMenu}/>
            </Detached>
          );
        }
      });
    }
  }

  renderItemList(hasItemList) {
    if (hasItemList) {
      return React.Children.map(this.props.children, (child) => {
        if (child && child.type && child.type.displayName === 'ItemList' && this.state.openInnerList) {
          return child;
        }
      });
    }
  }

  render() {
    const hasItemList = this.checkIfItemListExists();
    const hasMenu = this.checkIfMenuExists();
    const childrenOutput = {};
    childrenOutput.menu = this.renderMenu(hasMenu);
    childrenOutput.itemList = this.renderItemList(hasItemList);
    return (
      <li onMouseOver={this.props.subMenuMode ? ()=>{} : this.openMenu}
          onClick={this.props.subMenuMode && this.props.subMenuMode === 'click' ? this.toggleMenu : ()=>{}}>
        {this.renderLabel(hasMenu, hasItemList)}
        {createFragment(childrenOutput)}
      </li>
    );
  }
}