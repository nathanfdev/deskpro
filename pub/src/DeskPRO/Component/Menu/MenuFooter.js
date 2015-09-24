import React from 'react';

export default class MenuFooter extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node,
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const widgetClass = this.props.widgetClass ? this.props.widgetClass : '';

    const displayClass = this.props.overrideWidgetClass
                          ? widgetClass
                          : 'dpw-navigation-dropdown-item dpw-navigation-dropdown-footer ' + widgetClass;

    return (<li>
      <div className={displayClass}>
        {this.props.children}
      </div>
    </li>);
  }
}
