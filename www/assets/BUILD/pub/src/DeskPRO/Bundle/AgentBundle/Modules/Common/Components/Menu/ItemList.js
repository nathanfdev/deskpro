import PropTypes from 'prop-types';
import React from 'react';

export class ItemList extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass:         PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children:            PropTypes.node
  };

  static displayName = 'ItemList';

  render() {
// dpw-navigation-dropdown-column-list-v2 must be a widgetClass prop, because in some Item list we don't need this class
    return (
      <li className={this.props.widgetClass}>
        <div className="dpw-navigation-dropdown-column-list">
          <ul>
            {React.Children.map(this.props.children, (child, index) => React.cloneElement(child, {
              widgetClass:         'dpw-navigation-dropdown-column-list-item',
              overrideWidgetClass: true,
              listItem:            true,
              key:                 index
            }))}
          </ul>
        </div>
      </li>
    );
  }
}
