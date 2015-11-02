import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';
import { pureRender } from 'Ampliflux';
import { connect } from 'react-redux';
import { updateRoutingState } from '../../../Application/Actions/routingActions';


class BaseList extends Component {
  renderCount(count, active) {
    if (count >= 0) {
      const classes = classNames('list-counter', { 'active': active });

      return (
        <div className="list-counter-bucket">
          <a className={classes} href="#">{count}</a>
        </div>
      );
    }
  }
}

export class ListSection extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <section className="sidebar-list">
        {this.props.children}
      </section>
    );
  }
}

@pureRender
export class ListItem extends BaseList {
  static propTypes = {
    children: PropTypes.node,
    count: PropTypes.number.isRequired,
    label: PropTypes.string,
    active: PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { children, count, active, onClick } = this.props;
    const classes = classNames('item', { 'active': active });

    let label = this.props.label;
    let nested = '';

    if (children instanceof Array && children.length) {
      children.forEach(child => {
        if (child.props.part === 'label') {
          label = child;
        } else if (child.props.part === 'nested') {
          nested = child;
        }
      });
    } else if (children instanceof Object && children.props.part === 'label') {
      label = children;
    } else {
      nested = children;
    }

    return (
      <li className="counter-display">
        {this.renderCount(count, active)}
        <a href="#" className={classes} onClick={onClick}>
          {label}
        </a>

        {nested}
      </li>
    );
  }
}

@connect(state => ({ state: state.Application.routing.get('hash') }))
export class ListItemStatefulContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired,
    groupId: PropTypes.string.isRequired,
    itemId: PropTypes.string.isRequired
  };

  render() {
    const props = this.props;
    const newProps = {
      ...props,

      // declaring "active" property accordingly to the URL state
      active: props.state.getIn([props.groupId, 'active']) === props.itemId,

      // decorating original "onClick" with additional URL state saving functionality
      onClick(event) {
        props.onClick(event);
        props.dispatch(updateRoutingState(props.groupId, 'active', props.itemId));
      }
    };

    return (
      <ListItem {...newProps} />
    );
  }
}

export class NestedList extends BaseList {
  static propTypes = {
    onClick: PropTypes.func.isRequired,
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

  getListItemParts(nested, group, depth) {
    const hasNested = nested && nested.length;

    const parts = {};
    if (hasNested || this.props.alwaysExpanded) {
      const expanded = this.state.expanded.indexOf(group) > -1;
      parts.label = (
        <span className="icon">
          <i className={'fa fa-caret-' + (expanded ? 'down' : 'right')}></i>
          {this.props.groups[group]}
        </span>
      );
      parts.nested = this.renderNested(nested, group, depth);
    } else {
      parts.label = this.props.groups[group];
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

  renderNested(nested, group, depth) {
    const hasNested = nested && nested.length;
    const isExpanded = this.props.alwaysExpanded || this.state.expanded.indexOf(group) > -1;

    if (hasNested && isExpanded) {
      return (
        <ul className={'with-connectors depth-' + depth}>
          {nested.map(item => this.renderListItem(item, depth + 1))}
        </ul>
      );
    }
  }

  renderListItem({nested, group, count}, depth) {
    this.ensureValidDepth(depth);
    const parts = this.getListItemParts(nested, group, depth);

    return (
      <ListItem key={group} count={count} onClick={this.toggleExpanded(group)}>
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
