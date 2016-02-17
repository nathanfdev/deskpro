import React from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';
import { UrgencyList } from './UrgencyList';
import Immutable from 'immutable';

export class NestedListContainer extends BaseNestedList {
  renderListItem(item, depth) {
    this.ensureValidDepth(depth);
    const { nested, id, count, type, title, parent, parentTitle } = item;
    const props = {
      count,
      id,
      type,
      title,
      parent,
      parentTitle,
      isTopLevel: depth === 1,
      listFilters: parent ? { filter: parent, [type]: id } : { filter: id }
    };

    const isGroupedByUrgency = nested && nested.length && item.grouped_by === 'urgency';
    const content = isGroupedByUrgency
      ? <UrgencyList items={Immutable.fromJS(nested)}/>
      : this.renderNested(item, depth);

    return (
      <ListItemContainer {...props} key={id}>
        {content}
      </ListItemContainer>
    );
  }

  renderNested(item, depth) {
    const { nested, id, title } = item;
    const hasNested = nested && nested.length;
    const isExpanded = this.props.alwaysExpanded || this.state.expanded.indexOf(id) > -1;
    if (hasNested && isExpanded) {
      return (
        <ul className={'with-connectors depth-' + depth}>
          {nested.map(nestedItem => this.renderListItem({ ...nestedItem, parent: id, parentTitle: title}, depth + 1))}
        </ul>
      );
    }
  }
}
