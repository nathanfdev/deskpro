import React, { PropTypes } from 'react';
import ListElement from 'Component/List/ListElement';

class List extends React.Component {
  static propTypes = {
    elements: PropTypes.arrayOf(PropTypes.shape({
      label: PropTypes.string,
      icon: PropTypes.string
    }))
  };

  constructor(props) {
    super(props);
  }

  getItems() {
    let items = [];
    const {elements} = this.props;
    for (const i in elements) {
      if (!elements.hasOwnProperty(i)) {
        continue;
      }
      let props = elements[i];
      props['key'] = String(i);
      items.push(<ListElement {...props} />);
    }
    return items;
  }

  render() {
    return <div className="ui list">
      {this.getItems()}
    </div>
  }
}
export default List