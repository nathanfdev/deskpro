import React, { PropTypes } from 'react';
import ListElement from './ListElement';

class List extends React.Component {
  static propTypes = {
    elements: PropTypes.arrayOf(PropTypes.shape({
      label: PropTypes.string,
      icon:  PropTypes.string
    })),
    children: PropTypes.node
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
    const { children } = this.props;
    return (<div className="ui list">
      {this.getItems()}
      {children}
    </div>);
  }
}
export default List;
