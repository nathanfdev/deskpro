import React, {Component, PropTypes} from 'react';

export default class ItemFormat extends Component {
  static propTypes = {
    icon: PropTypes.string,
    itemType: PropTypes.string,
    widgetClass: PropTypes.string,
    checked: PropTypes.bool,
    children: PropTypes.any,
    listItem: PropTypes.bool,
    hasMenu: PropTypes.bool,
    hasItemList: PropTypes.bool,
    toggleInnerList: PropTypes.func
  };

  renderIcon() {
    if (this.props.icon) {
      return (
        <span className="dpw-navigation-dropdown-item-mark">
            <span className="dpw-navigation-dropdown-item-icon dpw-navigation-dropdown-item-icon-2x">
              <i className={'fa fa-' + this.props.icon}/>
            </span>
        </span>
      );
    }
  }

  renderMenuCaret() {
    if (this.props.hasMenu) {
      return (
        <span className="dpw-navigation-dropdown-item-status">
            <i className="fa fa-caret-right menu-submenu-caret"/>
          </span>
      );
    }
  }

  renderChecked() {
    if (this.props.checked) {
      return (
        <span className="dpw-navigation-dropdown-item-status">
            <i className="fa fa-check"/>
        </span>
      );
    }
  }

  renderInnerListToggle() {
    if (this.props.hasItemList) {
      return (
        <span className="dpw-navigation-dropdown-item-expand" onClick={this.props.toggleInnerList}>
            <i className="fa fa-caret-down"/>
          </span>
      );
    }
  }

  renderChildren() {
    if (this.props.listItem) {
      return (
        <div>
        <span className="dpw-navigation-dropdown-column-list-disc">
          <i className="fa fa-circle"/>
        </span>
        <span className="dpw-navigation-dropdown-column-list-title">
          {this.props.children}
        </span>
        </div>
      );
    }
    return (
      <div>
        {this.renderIcon()}
        <span className="dpw-navigation-dropdown-item-title">
          {this.props.children}
        </span>
        {this.renderMenuCaret()}
        {this.renderChecked()}
        {this.renderInnerListToggle()}
      </div>
    );
  }

  // @ToDo Why we need that stuff with typeClass calculation? We never use it later
  // https://github.com/DeskPRO/DeskPRO/blob/fe6f1a0c3e663e5e597dc306b27a0d1f1fc117c5/pub/src/DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemFormat.js
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

    return this.renderChildren();
  }
}
