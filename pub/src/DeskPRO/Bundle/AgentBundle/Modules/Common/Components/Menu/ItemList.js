import React from 'react';

export class ItemList extends React.Component {

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
// dpw-navigation-dropdown-column-list-v2 must be a widgetClass prop, because in some Item list we don't need this class
    return (
      <li className={this.props.widgetClass}>
        <div className="dpw-navigation-dropdown-column-list">
          <ul>
            {React.Children.map(this.props.children, (child, index) => {
              return React.cloneElement(child, {
                widgetClass: 'dpw-navigation-dropdown-column-list-item',
                overrideWidgetClass: true,
                listItem: true,
                key: index
              });
            })}
          </ul>
        </div>
      </li>
    );
  }
}