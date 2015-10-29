import React from 'react';
import { NestedList as BaseNestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class NestedList extends BaseNestedList {
  renderListItem({nested, group, count, grouped_by}, depth) {
    this.ensureValidDepth(depth);

    const props = {
      count,
      group,
      grouped_by,
      editable: depth === 1
    };

    let result;

    if (nested && nested.length && nested[0].grouped_by === 'urgency') {
      console.log('ToDo: Render list grouped by urgency somehow differently');

      result = (
        <ListItemContainer {...props}>
          {this.renderNested(nested, group, depth)}
        </ListItemContainer>
      );
    } else {
      result = (
        <ListItemContainer {...props}>
          {this.renderNested(nested, group, depth)}
        </ListItemContainer>
      );
    }

    return result;
  }
}
