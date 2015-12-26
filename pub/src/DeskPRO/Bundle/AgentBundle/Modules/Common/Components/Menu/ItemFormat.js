import React from 'react';
import classNames from 'classnames';

export class ItemFormat extends React.Component {
  static propTypes = {
    icon: React.PropTypes.string,
    label: React.PropTypes.string,
    format: React.PropTypes.string,
    itemType: React.PropTypes.string,
    filterType: React.PropTypes.string,
    widgetClass: React.PropTypes.string,
    checked: React.PropTypes.bool,
    isActive: React.PropTypes.bool,
    discMarked: React.PropTypes.bool,
    children: React.PropTypes.any,
    listItem: React.PropTypes.bool,
    hasMenu: React.PropTypes.bool,
    hasItemList: React.PropTypes.bool,
    selected: React.PropTypes.array,
    resetFilter: React.PropTypes.func,
    renderFilterInfo: React.PropTypes.func,
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

  renderLabel() {
    const { label } = this.props;
    if (label) {
      return (
        <span className="dpw-navigation-dropdown-item-title">
        {label}
      </span>
      );
    }
  }

  renderChildren() {
    if (this.props.children) {
      return React.Children.map(this.props.children, (child) => {
        if (child && child.type && child.type.displayName !== 'ItemList' && child.type.displayName !== 'Menu') {
          return child;
        }
      });
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

  renderFilterClear() {
    const { isActive, format, resetFilter, filterType} = this.props;
    if (isActive && format === 'filter') {
      return (
        <span className="dpw-navigation-dropdown-item-clear" onClick={resetFilter.bind(null, filterType)}>
          <i className="fa fa-times"></i>
        </span>
      );
    }
  }

  render() {
    const {selected, format, renderFilterInfo} = this.props;
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
        {this.renderDiscMark()}
        {this.renderIcon()}
        {this.renderLabel()}
        {format === 'filter' && selected && renderFilterInfo(selected)}
        {this.renderChildren()}
        {this.renderSubmenuCaret()}
        {this.renderCheckedMark()}
        {this.renderInnerListSwitcher()}
        {this.renderFilterClear()}
      </div>
    );
  }
}