import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';

export class NestedList extends React.Component {

  render() {
    const { node, label, onClick, status, currentGroup } = this.props;
    var active  = currentGroup.name === 'status' && currentGroup.value === status;
    var classes = classNames('item', {
      'active': active
    });
    return (
      <li className="counter-display" onClick={onClick.bind(this, {name: 'status', value: status})}>
        <RenderCount count={node.count} active={active}/>
        <a href="#" className={classes}>{label}</a>
        {this.renderChildren(node.nested, status)}
      </li>
    );
  }


  renderCount(count, active) {
    if ((count !== 0) && !count) {
      return;
    }
    var classes = classNames('list-counter', {
      'active': active
    });
    return (
      <div className="list-counter-bucket">
        <a className={classes} href="#">{count}</a>
      </div>
    );
  }

  renderChildren(nested) {
    if (nested.length === 0) {
      return;
    }
    const {status, currentGroup, onClick} = this.props;
    return (
      <ul className="with-connectors">
        {nested.map((item, index) => <ChildListItem
            key={index}
            item={item}
            currentGroup={currentGroup}
            onClick={onClick}
            status={status}
            />
        )}
      </ul>
    );
  }

}

export class ChildListItem extends React.Component {
  render() {
    const { item, status, onClick, currentGroup } = this.props;
    var active      = false,
          groupName = 'status_category';
    if (status !== 'hidden') {
      active = currentGroup.name === groupName && currentGroup.value === item.group;
    }
    else {
      groupName = 'hidden_status';
      active    = currentGroup.name === groupName && currentGroup.value === item.group;
    }
    var classes = classNames('item', {
      'active': active
    });
    return (
      <li onClick={onClick.bind(this, {name: groupName, value: item.group})}>
        <RenderCount count={item.count} active={active}/>
        <a href="#" className={classes}>{item.group}</a>
      </li>
    );
  }
}

export class RenderCount extends React.Component {
  render() {
    const { count, active } = this.props;
    if ((count !== 0) && !count) {
      return;
    }
    var classes = classNames('list-counter', {
      'active': active
    });
    return (
      <div className="list-counter-bucket">
        <a className={classes} href="#">{count}</a>
      </div>
    );
  }
}