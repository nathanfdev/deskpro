import React from 'react';
import BaseItem from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/BaseItem';
import classNames from 'classnames';

export default class Item extends React.Component {
  static propTypes = {
    icon: React.PropTypes.string,
    itemType: React.PropTypes.string,
    widgetClass: React.PropTypes.string,
    checked: React.PropTypes.bool,
    children: React.PropTypes.any,
    listItem: React.PropTypes.bool,
    closeMenu: React.PropTypes.func,
    activeItem: React.PropTypes.object,
    isActive: React.PropTypes.bool
  };

  render() {
    let typeClass = classNames({
      'dpw-navigation-dropdown-item-grey': this.props.itemType === 'locked',
      'dpw-navigation-dropdown-item-lock': this.props.itemType === 'locked',
      'dpw-navigation-dropdown-item-greyer': this.props.itemType === 'danger',
      'dpw-navigation-dropdown-item-warning': this.props.itemType === 'danger'
    });

    if (this.props.widgetClass) {
      typeClass += ' ' + this.props.widgetClass;
    }

    return (<BaseItem {...this.props} widgetClass={typeClass} format="item">
      {this.props.children}
    </BaseItem>);
  }
}
