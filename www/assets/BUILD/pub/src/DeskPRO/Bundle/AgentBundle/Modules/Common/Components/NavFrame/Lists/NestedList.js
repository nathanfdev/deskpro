import PropTypes from 'prop-types';
import React from 'react';
import { ListItem } from './ListItem';

export class NestedList extends React.Component {

  static propTypes = {
    onClick:            PropTypes.func,
    onItemControlClick: PropTypes.func,
    items:              PropTypes.array,
    depth:              PropTypes.number,
    alwaysExpanded:     PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: []
    };
  }

  getListItemParts(item) {
    const { nested, id } = item;
    const hasNested = nested && nested.length;

    const parts = {};
    const label = item.title ? item.title : '—';
    if (hasNested) {
      const expanded = this.state.expanded.indexOf(id) > -1;
      const caret = this.props.alwaysExpanded ? '' : <i className={'fa fa-caret-' + (expanded ? 'down' : 'right')}></i>;
      parts.label = <span className="icon">{caret} {label}</span>;
      parts.nested = this.renderNested(item);
    } else {
      parts.label = label;
      parts.nested = '';
    }

    return parts;
  }

  // nested list rendering recursion max depth
  static maxDepth = 10;

  toggleExpanded(id) {
    return event => {
      event.preventDefault();

      if (this.props.alwaysExpanded) {
        return;
      }

      const expanded = [...this.state.expanded];

      const index = expanded.indexOf(id);
      if (index > -1) {
        expanded.splice(index, 1);
      } else {
        expanded.push(id);

        // perform onClick when expanding a list item
        if (this.props.onClick) {
          this.props.onClick(id);
        }
      }

      this.setState({ expanded });
    };
  }

  ensureValidDepth(depth) {
    if (depth > NestedList.maxDepth) {
      throw new Error(`NestedList maximum recursion depth ${NestedList.maxDepth} exceeded`);
    }
  }

  renderNested(item) {
    const { nested, id, depth = 0 } = item;
    const hasNested = nested && nested.length;
    const isExpanded = this.props.alwaysExpanded || this.state.expanded.indexOf(id) > -1;

    if (hasNested && isExpanded) {
      return (
        <ul className={`with-connectors depth-${depth}`}>
          {nested.map((child, key) => this.renderListItem({
            ...child,
            parent: id
          }, depth + 1, key))}
        </ul>
      );
    }
  }

  renderListItem(item, depth, key) {
    this.ensureValidDepth(depth);

    const parts = this.getListItemParts(item);
    const { id, count } = item;
    const { onItemControlClick } = this.props;

    return (
      <ListItem
        key={key}
        count={count}
        onClick={this.toggleExpanded(id)}
        onItemControlClick={onItemControlClick ? onItemControlClick(id) : null}
      >
        <div part="label">{parts.label}</div>
        <div part="nested">{parts.nested}</div>
      </ListItem>
    );
  }

  render() {
    const depth = this.props.depth || 1;
    const className = depth > 1
      ? 'with-connectors depth-' + (depth - 1)
      : 'depth-0';

    if (!this.props.items || !this.props.items.length) {
      return <div></div>;
    }

    return (
      <ul className={className}>
        {this.props.items.map((item, key) => this.renderListItem(item, depth, key))}
      </ul>
    );
  }
}
