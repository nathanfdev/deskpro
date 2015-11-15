import React from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
import { UrgencyList } from './UrgencyList';
import Immutable from 'immutable';

export class NestedListContainer extends BaseNestedList {
  renderListItem(item, depth, parentIsLoading) {
    this.ensureValidDepth(depth);
    const { nested, group, count, grouped_by, parent } = item;

    const props = {
      count,
      group,
      grouped_by,
      isTopLevel: depth === 1,
      listFilters: parent ? {filter: parent, [grouped_by]: group} : {filter: group},
      parentIsLoading
    };

    const isGroupedByUrgency = nested && nested.length && nested[0].grouped_by === 'urgency';
    const content = isGroupedByUrgency
      ? <UrgencyList items={Immutable.fromJS(nested)} />
      : this.renderNested(item, depth);

    return (
      <ListItemContainer {...props}>
        {content}
      </ListItemContainer>
    );
  }

  renderNested(item, depth) {
    const { nested, group } = item;
    const hasNested = nested && nested.length;
    const isExpanded = this.props.alwaysExpanded || this.state.expanded.indexOf(group) > -1;
    if (hasNested && isExpanded) {
      return (
        <ul className={'with-connectors depth-' + depth}>
          {nested.map(nestedItem => this.renderListItem({...nestedItem, parent: group}, depth + 1))}
        </ul>
      );
    }
  }
}
