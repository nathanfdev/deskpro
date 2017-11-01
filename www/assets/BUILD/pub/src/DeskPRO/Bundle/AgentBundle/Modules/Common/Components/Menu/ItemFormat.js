import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class ItemFormat extends React.Component {
  static propTypes = {
    icon:             PropTypes.string,
    label:            PropTypes.string,
    format:           PropTypes.string,
    itemType:         PropTypes.string,
    filterType:       PropTypes.string,
    widgetClass:      PropTypes.string,
    checked:          PropTypes.bool,
    isActive:         PropTypes.bool,
    discMarked:       PropTypes.bool,
    children:         PropTypes.any,
    listItem:         PropTypes.bool,
    hasMenu:          PropTypes.bool,
    hasItemList:      PropTypes.bool,
    selected:         PropTypes.array,
    resetFilter:      PropTypes.func,
    renderFilterInfo: PropTypes.func,
    toggleInnerList:  PropTypes.func
  };

  renderDiscMark() {
    const classes = classNames('dpw-navigation-dropdown-item-disc', {
      'dpw-navigation-dropdown-item-disc-active': this.props.isActive
    });
    if (this.props.discMarked) {
      return (
        <span className="dpw-navigation-dropdown-item-mark">
            <span className={classes} />
        </span>
      );
    }
    return null;
  }

  renderIcon() {
    const { icon } = this.props;
    if (icon) {
      return (
        <span className="dpw-navigation-dropdown-item-mark">
            <span className="dpw-navigation-dropdown-item-icon dpw-navigation-dropdown-item-icon-2x">
              <i className={`fa fa-${icon}`} />
            </span>
        </span>
      );
    }
    return null;
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
    return null;
  }

  renderChildren() {
    const { children } = this.props;
    if (children) {
      return React.Children.map(children, child => {
        if (child && child.type && child.type.displayName !== 'ItemList' && child.type.displayName !== 'Menu') {
          return child;
        }
        return null;
      });
    }
    return null;
  }

  renderSubmenuCaret() {
    if (this.props.hasMenu) {
      return (
        <span className="dpw-navigation-dropdown-item-status">
          <i className="fa fa-caret-right menu-submenu-caret" />
        </span>
      );
    }
    return null;
  }

  renderCheckedMark() {
    if (this.props.checked) {
      return (
        <span className="dpw-navigation-dropdown-item-status">
            <i className="fa fa-check" />
        </span>
      );
    }
    return null;
  }

  renderInnerListSwitcher() {
    if (this.props.hasItemList) {
      return (
        <span className="dpw-navigation-dropdown-item-expand" onClick={this.props.toggleInnerList}>
            <i className="fa fa-caret-down" />
        </span>
      );
    }
    return null;
  }

  renderFilterClear() {
    const { isActive, format, resetFilter, filterType } = this.props;
    if (isActive && format === 'filter') {
      return (
        <span className="dpw-navigation-dropdown-item-clear" onClick={resetFilter.bind(null, filterType)}>
          <i className="fa fa-times" />
        </span>
      );
    }
    return null;
  }

  render() {
    const { selected, format, renderFilterInfo, widgetClass, itemType } = this.props;
    let typeClass = '';

    if (itemType) {
      switch (itemType) {
        case ('locked'):
          typeClass = 'dpw-navigation-dropdown-item-grey dpw-navigation-dropdown-item-lock';
          break;
        case ('danger'):
          typeClass = 'dpw-navigation-dropdown-item-greyer dpw-navigation-dropdown-item-warning';
          break;
        default:
          break;
      }

      if (typeClass && widgetClass) {
        typeClass = `${widgetClass} ${typeClass}`;
      }
    }

    if (this.props.listItem) {
      return (
        <div>
        <span className="dpw-navigation-dropdown-column-list-disc">
          <i className="fa fa-circle" />
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
