import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export default class ItemGroup extends Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: PropTypes.string,
    overrideWidgetClass: PropTypes.bool,
    children: PropTypes.node,
    menuLevel: PropTypes.number
  };

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const divClass = classNames(this.props.widgetClass,
      {'navigation-item-group': !this.props.overrideWidgetClass}
    );

    return (
      <ul className={divClass}>
        {React.Children.map(this.props.children, (child) => {
          return React.cloneElement(child, this.props);
        })}
      </ul>
    );
  }
}
