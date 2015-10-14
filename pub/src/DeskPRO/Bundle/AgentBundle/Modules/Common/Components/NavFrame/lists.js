import React, { Component, PropTypes } from 'react';
import classNames from 'classnames';
import { pureRender } from 'Ampliflux';
import { connect } from 'react-redux';
import { updateHashState } from '../../../Application/Actions/routingActions';


class BaseList extends Component {
  renderCount(count, active) {
    if ((count !== 0) && !count) {
      return;
    }
    var classes = classNames('list-counter', {'active': active});

    return (
      <div className="list-counter-bucket">
        <a className={classes} href="#">{count}</a>
      </div>
    );
  }
}

@pureRender
export class ListItem extends BaseList {
  static propTypes = {
    children: PropTypes.node,
    count: PropTypes.number.isRequired,
    label: PropTypes.string.isRequired,
    active: PropTypes.bool.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { count, label, active, onClick } = this.props;
    const classes = classNames('item', {'active': active});

    return (
      <li className="counter-display">
        {this.renderCount(count, active)}
        <a href="#" className={classes} onClick={onClick}>{label}</a>

        {this.props.children}
      </li>
    );
  }
}

@connect(state => ({state: state.Application.routing.get('hash')}))
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
      onClick: function(event) {
        props.onClick(event);
        props.dispatch(updateHashState(props.groupId, 'active', props.itemId));
      }
    };

    return (
      <ListItem {...newProps} />
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