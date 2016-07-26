import React, { PropTypes } from 'react';
import classNames from 'classnames';
import MenuItem from 'Component/Menu/MenuItem';

class Menu extends React.Component {
  static propTypes = {
    title: PropTypes.string,
    withDivider: PropTypes.bool,
    items: PropTypes.arrayOf(
      PropTypes.object
    )
  };

  getItems() {
    let menus = [];
    const {items} = this.props;
    for (const i in items) {
      if (!items.hasOwnProperty(i)) {
        continue;
      }
      let props = items[i];
      props['key'] = String(i);
      menus.push(<MenuItem {...props} />)
    }
    return menus;
  }

  getTitle() {
    const {title} = this.props;
    if (title)
      return <h4>{title}</h4>;
    return null;
  }

  render() {
    const {withDivider} = this.props;
    return <div className={classNames('item', {'with-divider': withDivider})}>
      {this.getTitle()}
      <div className="menu">
        {this.getItems()}
      </div>
    </div>
  }
}
export default Menu;