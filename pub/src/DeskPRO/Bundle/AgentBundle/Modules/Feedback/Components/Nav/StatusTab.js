import React, { Component, PropTypes } from 'react';
import { FeedbackListItem } from './FeedbackListItem';
import { NestedList } from './NestedList';

export class StatusTab extends Component {

  static propTypes = {
    statuses: PropTypes.array.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { statuses } = this.props;
    return (
      <ul>
        <FeedbackListItem
          label="New"
          count={statuses.new}
          listOptions={{status: 'new'}}
        />
        <NestedList node={statuses.active} status="active" label="Active"/>
        <NestedList node={statuses.closed} status="closed" label="Closed"/>
        <NestedList node={statuses.hidden} status="hidden" label="Hidden"/>
      </ul>
    );
  }
}