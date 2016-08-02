import React, { PropTypes } from 'react';
import classNames from 'classnames';
import MenuItem from './MenuItem';

class Menu extends React.Component {
  static propTypes = {
    title:       PropTypes.string,
    withDivider: PropTypes.bool,
    filterText:  PropTypes.string,
    classNames:  PropTypes.object,
    items:       PropTypes.arrayOf(
      PropTypes.object
    ),
    children: PropTypes.node
  };

  getItems() {
    const menus = [];
    const { items } = this.props;
    let i = 0;
    for (const props of items) {
      if (props.label.toLowerCase().indexOf(this.props.filterText.toLowerCase()) === -1) {
        continue;
      }
      props.key = String(i++);
      menus.push(<MenuItem {...props} />);
    }
    return menus;
  }

  getTitle() {
    const { title } = this.props;
    if (title) {
      return <h4>{title}</h4>;
    }
    return null;
  }

  render() {
    const { withDivider, children } = this.props;
    return (<div className={classNames('item', { 'with-divider': withDivider })}>
      {this.getTitle()}
      <div className="menu">
        {this.getItems()}
        {children}
      </div>
    </div>);
  }
}
export default Menu;
