import React from 'react';

export default class Card extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    widgetClass: React.PropTypes.string,
    overrideWidgetClass: React.PropTypes.bool,
    children: React.PropTypes.node
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const baseClass = 'dpw-navigation-dropdown';

    let divClass = (this.props.widgetClass ? baseClass + ' ' + this.props.widgetClass : baseClass);

    if (this.props.overrideWidgetClass) {
      divClass = this.props.widgetClass;
    }

    return (<div className={divClass}>
        <ul>{this.props.children}</ul>
      </div>);
  }
}
