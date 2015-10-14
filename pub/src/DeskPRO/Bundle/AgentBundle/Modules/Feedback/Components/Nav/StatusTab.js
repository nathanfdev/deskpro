import React, { Component, PropTypes } from 'react';
import { FeedbackListItem } from './FeedbackListItem';
import { NestedList } from './NestedList';

export class StatusTab extends Component {

  static propTypes = {
    statuses: PropTypes.array.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { statuses, onClick, currentGroup } = this.props;
    return (
      <ul>
        <FeedbackListItem
          label="New"
          count={statuses.new}
          onClick={onClick({name: 'status', value: 'new'})}
        />
        <NestedList currentGroup={currentGroup} node={statuses.active} onClick={onClick.bind(this)} status="active" label="Active"/>
        <NestedList currentGroup={currentGroup} node={statuses.closed} onClick={onClick.bind(this)} status="closed" label="Closed"/>
        <NestedList currentGroup={currentGroup} node={statuses.hidden} onClick={onClick.bind(this)} status="hidden" label="Hidden"/>
      </ul>
    );
  }
}