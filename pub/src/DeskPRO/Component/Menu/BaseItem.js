import React from 'react';
import Menu from 'DeskPRO/Component/Menu/Menu';
import Positioned from 'DeskPRO/Component/Positioned';

export default class BaseItem extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node,
    onClick: React.PropTypes.func,
    onMouseOver: React.PropTypes.func,
    onMouseOut: React.PropTypes.func,
    subMenuMode: React.PropTypes.string,
    parentMenuLevel: React.PropTypes.number,
    disabled: React.PropTypes.bool
  }

  /**
   * Constructor
   * @param  {Object} props The component props
   * @return {void}
   */
  constructor(props) {
    super(props);

    this.state = {
      openMenu: false
    };
  }

  /**
   * Open the menu
   * @return {void}
   */
  openMenu() {
    this.setState({
      openMenu: true
    });
  }

  /**
   * Close the menu
   * @return {void}
   */
  closeMenu() {
    this.setState({
      openMenu: false
    });
  }

  /**
   * Toggle the menu open/closed
   * @return {void}
   */
  toggleMenu() {
    this.setState({
      openMenu: !this.state.openMenu
    });
  }

//  onMouseLeave={this.closeMenu.bind(this)}

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const items = typeof this.props.children.map === 'function' ? this.props.children : [this.props.children];
    const baseClass = 'dpw-navigation-dropdown-item';

    const onClickAction = this.props.onClick ? this.props.onClick : () => {};
    const onMouseOverAction = this.props.onMouseOver ? this.props.onMouseOver : () => {};
    const onMouseOutAction = this.props.onMouseOut ? this.props.onMouseOut : () => {};

    const divClasses = [baseClass];

    if (this.props.widgetClass) {
      divClasses.push(this.props.widgetClass);
    }

    if (this.state && this.state.openMenu) {
      divClasses.push('active');
    }

    if (this.props.disabled) {
      divClasses.push('disabled');
    }

    let divClass = divClasses.join(' ');

    if (this.props.overrideWidgetClass) {
      divClass = this.props.widgetClass;
    }

    let output = [];

    // Loop through the children to work out what to do with menus
    if (this.props.children) {
      const children = typeof this.props.children.map === 'function' ? this.props.children : [this.props.children];

      // Calculate the raw menu items
      const contents = children.map((child) => {
        if (!child.type || (child.type.displayName !== 'Menu' && child.type.displayName !== 'ItemList')) {
          return child;
        }
      });

      output = [(<a className={divClass} href="#" onClick={onClickAction} onMouseOver={onMouseOverAction} onMouseOut={onMouseOutAction}>
        {contents}
      </a>)];

      const itemList = children.map((child) => {
        if (child.type && child.type.displayName === 'ItemList') {
          return child;
        }
      });

      // Calculate the menu
      const menu = children.map((child) => {
        if (child.type && child.type.displayName === 'Menu') {
          const parentLevel = this.props.parentMenuLevel ? this.props.parentMenuLevel : 1;
          const childProps = child.props;
          childProps.menuLevel = parentLevel + 1;

          return (<Positioned isOpen={this.state && this.state.openMenu}
                              positionMy="top left"
                              positionAt="top right"
                              positionTarget={this}
                              key={child}>
              <Menu {...childProps}
              isOpen={this.state && this.state.openMenu} />
          </Positioned>);
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
      return (<li onClick={this.toggleMenu.bind(this)}>{output}</li>);
    } else if (this.props.subMenuMode && this.props.subMenuMode === 'none') {
      return (<li>{output}</li>);
    }

    return (<li onMouseOver={this.openMenu.bind(this)} onMouseOut={this.closeMenu.bind(this)}>
      {output}
    </li>);
  }
}
