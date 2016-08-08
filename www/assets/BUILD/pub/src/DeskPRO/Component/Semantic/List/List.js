import React, { PropTypes } from 'react';
import classNames from 'classnames';
import ListElement from './ListElement';

class List extends React.Component {
  static propTypes = {
    elements: PropTypes.arrayOf(PropTypes.shape({
      label: PropTypes.string,
      icon:  PropTypes.string
    })),
    children: PropTypes.node,
    classes:  PropTypes.array
  };

  getItems() {
    const items = [];
    const { elements } = this.props;
    let i = 0;
    if (!elements) {
      return [];
    }
    for (const props of elements) {
      props.key = String(i++);
      items.push(<ListElement {...props} />);
    }
    return items;
  }

  render() {
    const { children, classes } = this.props;
    return (<div className={classNames('ui', 'list', classes)}>
      {this.getItems()}
      {children}
    </div>);
  }
}
export default List;
