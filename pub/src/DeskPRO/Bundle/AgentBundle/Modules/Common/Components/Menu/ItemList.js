import React, {Component, PropTypes} from 'react';

export default class ItemList extends Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children: PropTypes.node
  };

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const widgetClass = this.props.widgetClass ? this.props.widgetClass : '';

    const displayClass = this.props.overrideWidgetClass
                          ? widgetClass
                          : '' + widgetClass;

    return (
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {React.Children.map(this.props.children, (child, index) => {
            return React.cloneElement(child, {
              ...this.props,
              key: index,
              widgetClass: 'dpw-navigation-dropdown-column-list-item',
              overrideWidgetClass: true,
              listItem: true
            });
          })}
        </ul>
      </div>
    );
  }
}
