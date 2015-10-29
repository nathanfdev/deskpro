import React, {Component, PropTypes} from 'react';
export class StatusesCollection extends Component {

  static propTypes = {
    options: PropTypes.object.isRequired
  };

  renderListItem(item) {
    return (
      <ListItem label={item.group}>
        <NestedList node={item}/>
      </ListItem>
    );
  }

  render() {
    const { active, closed, hidden } = this.props.options.toJS();
    const {newFeedback} = this.props.options.toJS().new;
    // @todo Turn it in form of NestedList in the reducer
    // @todo Rename 'new' within statuses
    const items = [
      { ...newFeedback, group: 'New' },
      { ...active, group: 'Active' },
      { ...closed, group: 'Closed' },
      { ...hidden, group: 'Hidden' }
    ];

    return (
      <ul>
        {items.map(item => this.renderListItem(item))}
      </ul>
    );
  }
}

export class NestedList extends Component {

  static propTypes = {
    node: PropTypes.object.isRequired
  };

  renderNested(item) {
    return (
      <ListItem label={item.group}/>
    );
  }

  render() {
    const {node} = this.props;
    if (node.nested) {
      return (
        <ul>
          {node.nested.map(nestedItem=>this.renderNested(nestedItem))}
        </ul>
      );
    }
    return (<div/>);
  }
}

export class ListItem extends Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    children: PropTypes.any
  };

  render() {
    const {label} = this.props;
    return (
      <li>
        <div className="dpw--popup-item-person">
          <span className="dpw-popup-item-collection-name">
            {label}
          </span>
        </div>
        {this.props.children}
      </li>
    );
  }
}