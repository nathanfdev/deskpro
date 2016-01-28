import { createSelector } from 'reselect';
import { listFilterSelector } from '../RecordStores/Selectors/widgetSelectors';
import { agentNamesSelector } from '../../Agent/RecordStores/Selectors/agentsSelectors';

const stateSel = state => state.Example.list;

// Using agentNamesSelector as an example
// to demonstrate that you can combine state
// from anywhere to build a list however you see fit

export const listSelector = createSelector(
  listFilterSelector.recordsSel,
  agentNamesSelector,
  (list, agentNames) => {
    let items = [];

    list.forEach(w => {
      items.push({
        id: w.get('id'),
        name: w.get('name'),
        type: w.get('type'),
        inventory: w.get('inventory'),
        agentName: agentNames[w.get('id')] || 'No one'
      });
    });

    return items;
  }
);
