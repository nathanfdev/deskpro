import React from 'react';

export default class ItemGroup extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node,
    menuLevel: React.PropTypes.number
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const baseClass = 'navigation-item-group';

    let divClass = (this.props.widgetClass ? baseClass + ' ' + this.props.widgetClass : baseClass);

    if (this.props.overrideWidgetClass) {
      divClass = this.props.widgetClass;
    }

    return (<ul className={divClass}>
        {this.props.children}
      </ul>
    );
  }
}