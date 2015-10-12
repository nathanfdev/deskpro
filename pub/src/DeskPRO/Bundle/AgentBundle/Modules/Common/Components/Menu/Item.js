import React from 'react';
import BaseItem from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/BaseItem';

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
    let typeClass = '';

    if (this.props.itemType) {
      switch (this.props.itemType) {
        case ('locked'):
          typeClass = 'dpw-navigation-dropdown-item-grey dpw-navigation-dropdown-item-lock';
          break;
        case ('danger'):
          typeClass = 'dpw-navigation-dropdown-item-greyer dpw-navigation-dropdown-item-warning';
          break;
        default:
          break;
      }

      if (typeClass && this.props.widgetClass) {
        typeClass = this.props.widgetClass + ' ' + typeClass;
      }
    }

    return (<BaseItem {...this.props} widgetClass={typeClass} format="item">
      {this.props.children}
    </BaseItem>);
  }
}
