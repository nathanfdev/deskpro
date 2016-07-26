import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Menu } from 'Component/Menu/Menu';

export class MenuItem extends React.Component {
  static propTypes = {
    item: PropTypes.object.isRequired
  };

  render() {
    let subMenu = [];
    if (this.props.item.subContent) {
      subMenu.push(<i className="dropdown icon"/>);
      if (this.props.item.subContent.sections) {
        for (let section of this.props.item.subContent.sections) {
          subMenu.push(<Menu section={section} />);
        }
      }
    }
    let itemIcon = '';
    if (this.props.item.icon) {
      itemIcon = <i className={classNames('icon', this.props.item.icon)} />;
    }
    return <a className={classNames('ui', 'item', { dropdown: !!this.props.item.subContent })}>
      {itemIcon}
      {this.props.item.label}

      {subMenu}
      </a>
  }
}