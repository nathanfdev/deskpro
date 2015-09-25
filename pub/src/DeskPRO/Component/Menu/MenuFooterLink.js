import React from 'react';

export default class MenuFooterLink extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    icon: React.PropTypes.string,
    onClick: React.PropTypes.func,
    children: React.PropTypes.node
  }

  onClickAction() {
    if (this.props.onClick) {
      this.props.onClick();
    }
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    const iconClass = this.props.icon ? 'fa fa-' + this.props.icon : false;

    return (<div className="dpw-navigation-dropdown-options-link">
      <a href="#" onClick={this.onClickAction.bind(this)}>{this.props.children} {iconClass ? <i className={iconClass} /> : ''}</a>
    </div>);
  }
}
