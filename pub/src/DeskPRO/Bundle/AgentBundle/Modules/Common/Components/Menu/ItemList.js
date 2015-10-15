import React from 'react';

export default class ItemList extends React.Component {

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
      : '' + widgetClass;

// dpw-navigation-dropdown-column-list-v2 must be a widgetClass prop, because in some Item list we don't need this class
    return (<li className={displayClass}>
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {React.Children.map(this.props.children, (child) => {
            return React.cloneElement(child, {
              widgetClass: 'dpw-navigation-dropdown-column-list-item',
              overrideWidgetClass: true,
              listItem: true
            });
          })}
        </ul>
      </div>
    </li>);
  }
}