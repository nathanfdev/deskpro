import React, { PropTypes } from 'react';
import { ListItem } from './ListItem';

export class NestedList extends React.Component {

  static propTypes = {
    onClick: PropTypes.func,
    onItemControlClick: PropTypes.func,
    groups: PropTypes.object,
    items: PropTypes.object,
    depth: PropTypes.number,
    alwaysExpanded: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: []
    };
  }

  getListItemParts(item) {
    const { nested, group } = item;
    const hasNested = nested && nested.length;

    const parts = {};
    const label = this.props.groups[group] ? this.props.groups[group] : '—';
    if (hasNested) {
      const expanded = this.state.expanded.indexOf(group) > -1;
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

  toggleExpanded(group) {
    return event => {
      event.preventDefault();

      if (this.props.alwaysExpanded) {
        return;
      }

      const expanded = [...this.state.expanded];

      const index = expanded.indexOf(group);
      if (index > -1) {
        expanded.splice(index, 1);
      } else {
        expanded.push(group);

        // perform onClick when expanding a list item
        if (this.props.onClick) {
          this.props.onClick(group);
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
    const { nested, group, depth } = item;
    const hasNested = nested && nested.length;
    const isExpanded = this.props.alwaysExpanded || this.state.expanded.indexOf(group) > -1;

    if (hasNested && isExpanded) {
      return (
        <ul className={'with-connectors depth-' + depth}>
          {nested.map(child => this.renderListItem({...child, parent: group}, depth + 1))}
        </ul>
      );
    }
  }

  renderListItem(item, depth) {
    this.ensureValidDepth(depth);

    const parts = this.getListItemParts(item);
    const { group, count } = item;
    const { onItemControlClick } = this.props;

    return (
      <ListItem
        key={group}
        count={count}
        onClick={this.toggleExpanded(group)}
        onItemControlClick={onItemControlClick ? onItemControlClick(group) : null}
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
      : '';

    return (
      <ul className={className}>
        {this.props.items.map(item => this.renderListItem(item, depth))}
      </ul>
    );
  }
}
