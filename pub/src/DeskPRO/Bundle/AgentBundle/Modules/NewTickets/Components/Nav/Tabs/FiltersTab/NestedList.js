import React from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
import { UrgencyList } from './UrgencyList';
import Immutable from 'immutable';

export class NestedList extends BaseNestedList {
  renderListItem({nested, group, count, grouped_by}, depth) {
    this.ensureValidDepth(depth);

    const props = {
      count,
      group,
      grouped_by,
      editable: depth === 1
    };

    const isGroupedByUrgency = nested && nested.length && nested[0].grouped_by === 'urgency';
    const content = isGroupedByUrgency
      ? <UrgencyList items={Immutable.fromJS(nested)} />
      : this.renderNested(nested, group, depth);

    return (
      <ListItemContainer {...props}>
        {content}
      </ListItemContainer>
    );
  }
}
