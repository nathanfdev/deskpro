import React, { Component, PropTypes } from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class NestedList extends Component {

  render() {
    const { node, label, status } = this.props;
    return (
      <li className="counter-display">
        <RenderCount count={node.count} />
        <a href="#" className={'item'}>{label}</a>
        {this.renderChildren(node.nested, status)}
      </li>
    );
  }

  renderChildren(nested) {
    if (nested.length === 0) {
      return;
    }
    const {status} = this.props;
    return (
      <ul className="with-connectors">
        {nested.map((item, index) => <ChildListItem
            key={index}
            item={item}
            status={status}
            />
        )}
      </ul>
    );
  }

}

export class ChildListItem extends Component {
  render() {
    const { item, status } = this.props;
    let groupName = 'status_category';
    if (status === 'hidden') {
      groupName = 'hidden_status';
    }

    return (
      <li className={'onClick={onClick.bind(this, {name: groupName, value: item.group})}'}>
        <RenderCount count={item.count} />
        <a href="#" className={'item'}>{item.group}</a>
      </li>
    );
  }
}

export class RenderCount extends Component {
  render() {
    const { count } = this.props;
    if ((count !== 0) && !count) {
      return;
    }

    return (
      <div className="list-counter-bucket">
        <a className={'list-counter'} href="#">{count}</a>
      </div>
    );
  }
}