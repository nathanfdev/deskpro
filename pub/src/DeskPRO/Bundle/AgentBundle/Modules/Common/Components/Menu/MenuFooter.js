import React from 'react';
import classNames from 'classnames';

export class MenuFooter extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node
  };

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const widgetClass = this.props.widgetClass ? this.props.widgetClass : '';
    var classes = classNames(widgetClass, {
      'dpw-navigation-dropdown-item dpw-navigation-dropdown-footer': !this.props.overrideWidgetClass
    });

    return (<li>
      <div className={classes}>
        {this.props.children}
      </div>
    </li>);
  }
}
