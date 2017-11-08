import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import ListElement from './ListElement';

class List extends React.Component {
  static propTypes = {
    elements:  PropTypes.array,
    children:  PropTypes.node,
    className: PropTypes.string
  };

  static defaultProps = {
    className: '',
    elements:  []
  };

  getItems() {
    const items = [];
    const { elements } = this.props;
    let i = 0;
    if (!elements) {
      return [];
    }
    for (const props of elements) {
      props.key = String(i);
      i += 1;
      items.push(<ListElement {...props} />);
    }
    return items;
  }

  render() {
    const { children, className } = this.props;
    return (<div className={classNames('ui', 'list', className)}>
      {this.getItems()}
      {children}
    </div>);
  }
}
export default List;
