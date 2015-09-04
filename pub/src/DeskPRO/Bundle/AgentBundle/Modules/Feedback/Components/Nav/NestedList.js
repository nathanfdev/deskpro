import React, { Component, PropTypes } from 'react';

export class NestedList extends React.Component {
  renderCount(count) {
    if ((count !== 0) && !count) {
      return;
    }

    return (
      <div className="list-counter-bucket">
        <a className="list-counter active" href="#">{count}</a>
      </div>
    );
  }

  renderChildren(nested, onClick, status) {
    let itemKey = 0;
    if (nested.length === 0) {
      return;
    }

    return (
      <ul className="with-connectors">
        {nested.map(item =>
            <li onClick={onClick.bind(this, {'status':status,'status_category':item.group})}
                key={itemKey++}>
              {this.renderCount(item.count)}
              <a href="#" className="item">{item.group}</a></li>
        )}
      </ul>
    );
  }

  render() {
    const { node, label, onClick, status } = this.props;
    return (
      <li onClick={onClick.bind(this, {'status':status})}>
        {this.renderCount(node.count)}
        <a href="#" className="item">{label}</a>
        {this.renderChildren(node.nested, onClick, status)}
      </li>
    );

  }
}
