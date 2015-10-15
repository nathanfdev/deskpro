import React, { Component, PropTypes } from 'react';
import { ListItemContainer } from './ListItemContainer';
import { NestedList } from './NestedList';

export class StatusTab extends Component {

  static propTypes = {
    statuses: PropTypes.array.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { active, closed, hidden } = this.props.statuses;

    // @todo Turn it in form of NestedList in the reducer
    const items = [
      {...active, group: 'active'},
      {...closed, group: 'closed'},
      {...hidden, group: 'hidden'}
    ];

    return (
      <ul>
        <ListItemContainer
          label="New"
          count={this.props.statuses.new}
          listOptions={{status: 'new'}}
        />
        <NestedList items={items} alwaysExpanded />
      </ul>
    );
  }
}