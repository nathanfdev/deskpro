import React from 'react';
import classNames from 'classnames';

export default class ItemFormat extends React.Component {
  static propTypes = {
    icon: React.PropTypes.string,
    itemType: React.PropTypes.string,
    widgetClass: React.PropTypes.string,
    checked: React.PropTypes.bool,
    isActive: React.PropTypes.bool,
    discMarked: React.PropTypes.bool,
    children: React.PropTypes.any,
    listItem: React.PropTypes.bool,
    hasMenu: React.PropTypes.bool,
    hasItemList: React.PropTypes.bool,
    toggleInnerList: React.PropTypes.func
  };

  renderDiscMark() {
    const classes = classNames('dpw-navigation-dropdown-item-disc', {
      'dpw-navigation-dropdown-item-disc-active': this.props.isActive
    });
    if (this.props.discMarked) {
      return (
        <span className="dpw-navigation-dropdown-item-mark">
            <span className={classes}></span>
          </span>
      );
    }
  }

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

  renderSubmenuCaret() {
    if (this.props.hasMenu) {
      return (
        <span className="dpw-navigation-dropdown-item-status">
          <i className="fa fa-caret-right menu-submenu-caret"/>
        </span>
      );
    }
  }

  renderCheckedMark() {
    if (this.props.checked) {
      return (
        <span className="dpw-navigation-dropdown-item-status">
            <i className="fa fa-check"/>
          </span>
      );
    }
  }

  renderInnerListSwitcher() {
    if (this.props.hasItemList) {
      return (
        <span className="dpw-navigation-dropdown-item-expand" onClick={this.props.toggleInnerList}>
            <i className="fa fa-caret-down"/>
          </span>
      );
    }
  }

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

    if (this.props.listItem) {
      return (<div>
        <span className="dpw-navigation-dropdown-column-list-disc">
          <i className="fa fa-circle"/>
        </span>
        <span className="dpw-navigation-dropdown-column-list-title">
          {this.props.children}
        </span>
      </div>);
    }
    return (<div>
      {this.renderDiscMark()}
      {this.renderIcon()}
      <span className="dpw-navigation-dropdown-item-title">
        {this.props.children}
      </span>
      {this.renderSubmenuCaret()}
      {this.renderCheckedMark()}
      {this.renderInnerListSwitcher()}
    </div>);
  }
}