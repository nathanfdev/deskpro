import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import MenuItem from './MenuItem';

class Menu extends React.Component {
  static propTypes = {
    title:       PropTypes.string,
    withDivider: PropTypes.bool,
    filterText:  PropTypes.string,
    className:   PropTypes.string,
    items:       PropTypes.arrayOf(
      PropTypes.object
    ),
    children: PropTypes.node
  };
  static defaultProps = {
    items:     [],
    className: ''
  };

  getItems() {
    const menus = [];
    const { items } = this.props;
    let i = 0;
    for (const props of items) {
      if (props.label.toLowerCase().indexOf(this.props.filterText.toLowerCase()) !== -1) {
        props.key = String(i);
        i += 1;
        menus.push(<MenuItem {...props} />);
      }
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
    const { withDivider, children, className } = this.props;
    return (<div className={classNames('item', className, { 'with-divider': withDivider })}>
      {this.getTitle()}
      <div className="menu">
        {this.getItems()}
        {children}
      </div>
    </div>);
  }
}
export default Menu;
