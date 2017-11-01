import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { BaseItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/BaseItem';

export class Item extends Component {
  static propTypes = {
    icon:        PropTypes.string,
    itemType:    PropTypes.string,
    widgetClass: PropTypes.string,
    checked:     PropTypes.bool,
    children:    PropTypes.any,
    listItem:    PropTypes.bool,
    closeMenu:   PropTypes.func,
    activeItem:  PropTypes.object,
    isActive:    PropTypes.bool
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
