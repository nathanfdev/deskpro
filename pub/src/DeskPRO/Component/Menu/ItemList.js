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

    return (<li className={displayClass}>
      <div className="dpw-navigation-dropdown-column-list dpw-navigation-dropdown-column-list-v2">
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
