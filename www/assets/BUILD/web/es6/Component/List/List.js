import React, { PropTypes } from 'react';
import ListElement from 'Component/List/ListElement';

class List extends React.Component {
  static propTypes = {
    structure: PropTypes.shape({
      elements: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string,
        icon: PropTypes.string
      }))
    })
  };

  constructor(props) {
    super(props);
  }

  getItems() {
    let items = [];
    const elements = this.props.structure.elements;
    for (const i in elements) {
      const item = elements[i];
      let props = item;
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