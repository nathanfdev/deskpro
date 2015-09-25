import React from 'react';

export default class ItemFormat extends React.Component {
  static propTypes = {
    icon: React.PropTypes.string,
    itemType: React.PropTypes.string,
    widgetClass: React.PropTypes.string,
    checked: React.PropTypes.bool,
    children: React.PropTypes.any,
    listItem: React.PropTypes.bool
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

    return (<div>
      {this.props.listItem ?
      <div>
        <span className="dpw-navigation-dropdown-column-list-disc">
          <i className="fa fa-circle" />
        </span>
        <span className="dpw-navigation-dropdown-column-list-title">
          {this.props.children}
        </span>
      </div>
      :
      <div>
        { this.props.icon ?
          <span className="dpw-navigation-dropdown-item-mark">
            <span className="dpw-navigation-dropdown-item-icon dpw-navigation-dropdown-item-icon-2x">
              <i className={'fa fa-' + this.props.icon} />
            </span>
          </span>
        : '' }
   
        <span className="dpw-navigation-dropdown-item-title">
          {this.props.children}
        </span>
        {this.props.checked ?
          <span className="dpw-navigation-dropdown-item-status">
            <i className="fa fa-check" />
          </span>
        : ''}
      </div>
      }
      </div>);
  }
}
