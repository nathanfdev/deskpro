import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';


class BaseList extends Component {
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
}

export class ListItem extends BaseList {

  static propTypes = {
    count: PropTypes.number.isRequired,
    label: PropTypes.string.isRequired,
    active: PropTypes.bool.isRequired
  };

  render() {
    const {count, label, active } = this.props;
    const onClick = this.props.onClick ? this.props.onClick : () => {
    };

    var classes = classNames('item', {'active': active});

    return (
      <li>
        {this.renderCount(count)}
        <a href="#" className={classes} onClick={onClick}>{label}</a>

        {this.props.children}
      </li>
    );
  }
}

export class NestedList extends BaseList {

  // nested list rendering recursion max depth
  static maxDepth = 10;

  constructor(props) {
    super(props);

    this.state = {
      expanded: []
    }
  }

  render() {
    const depth     = this.props.depth || 1;
    const className = depth > 1
      ? 'with-connectors depth-' + (depth - 1)
      : '';

    return (
      <ul className={className}>
        {this.props.items.map(item => this.renderListItem(item, depth))}
      </ul>
    );
  }

  renderListItem({count, group, nested}, depth) {
    if (depth > NestedList.maxDepth) {
      throw 'NestedList maximum recursion depth exceeded'
    }

    const hasNested  = nested && nested.length;
    const isExpanded = this.state.expanded.indexOf(group) > -1;

    const renderNested = () => {
      if (!hasNested || !isExpanded) {
        return;
      }

      return (
        <ul className={'with-connectors depth-' + depth}>
          {nested.map(item => this.renderListItem(item, depth + 1))}
        </ul>
      );
    };

    const renderLabel = () => {
      const label = this.props.groups[group];

      if (hasNested) {
        const expanded = this.state.expanded.indexOf(group) > -1;
        return (
          <span className="icon"><i className={'fa fa-caret-' + (expanded ? 'down' : 'right')}></i> {label}</span>
        );
      } else {
        return label;
      }
    };

    return (
      <li key={group}>
        {this.renderCount(count)}
        <a href className="item" onClick={this.toggleExpanded(group).bind(this)}>
          {renderLabel()}
        </a>

        {renderNested()}
      </li>
    );
  }

  toggleExpanded(group) {
    return function (e) {
      e.preventDefault();

      let expanded = [...this.state.expanded];

      const i = expanded.indexOf(group);
      if (i > -1) {
        expanded.splice(i, 1);
      } else {
        expanded.push(group);

        // perform onClick when expanding a list item
        if (this.props.onClick) {
          this.props.onClick(group);
        }
      }

      this.setState({expanded});
    }
  }
}