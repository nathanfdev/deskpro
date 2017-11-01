import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class MenuFooter extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass:         PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children:            PropTypes.node
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
