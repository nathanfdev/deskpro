import React from 'react';
import classNames from 'classnames';

export class Menu extends React.Component {

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
  };

  render() {
    const isOpen = typeof this.props.isOpen !== 'undefined' ? this.props.isOpen : true;
    const divClass = classNames(this.props.widgetClass, { 'dpw-navigation-dropdown': !this.props.overrideWidgetClass });

    if (isOpen) {
      const menuLevel = this.props.menuLevel ? this.props.menuLevel : 1;

      return (
        <div className={divClass} style={{zIndex: 1000 + menuLevel}}>
          <ul>
            {this.props.children}
          </ul>
        </div>
      );
    }

    return <div />;
  }
}