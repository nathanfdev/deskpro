import React from 'react';

export default class Menu extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node,
    menuLevel: React.PropTypes.number,
    isOpen: React.PropTypes.bool
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const baseClass = 'dpw-navigation-dropdown';
    const isOpen = typeof this.props.isOpen !== 'undefined' ? this.props.isOpen : true;

    let divClass = (this.props.widgetClass ? baseClass + ' ' + this.props.widgetClass : baseClass);

    if (this.props.overrideWidgetClass) {
      divClass = this.props.widgetClass;
    }

    if (isOpen) {
      const menuLevel = this.props.menuLevel ? this.props.menuLevel : 1;
      return (<div className={divClass} style={{zIndex: 1000 + menuLevel}}>
          <ul>
            {React.Children.map(this.props.children, (child) => {
              return React.cloneElement(child, { parentMenuLevel: menuLevel });
            })}
          </ul>
        </div>);
    }

    return <div />;
  }
}
