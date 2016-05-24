import { createSelector } from 'reselect';
import { listParamsSelector } from './list';
import { labelsSelector } from './nav';

const navStateSelector = state => state.Tickets.nav;

export const listFiltersSelector = createSelector(
  [navStateSelector, listParamsSelector, labelsSelector],
  (navState, currentListParams, labels) => {
    const filterSelector = [
      { label: 'Date Created', type: 'date', fromParam: 'from', toParam: 'to' },
      {
        label:   'Status',
        type:    'select',
        param:   'status',
        options: [
          {
            value:  'new',
            label:  'New',
            nested: [
              { value: 'very_new', label: 'Very new' },
              { value: 'not_so_new', label: 'Not so new' }
            ]
          },
          { value: 'awaiting_agent', label: 'Awaiting agent' },
          { value: 'closed', label: 'Closed' }
        ]
      }
    ];
    // Labels options
    if (!currentListParams.get('label')) {
      const labelsFilter = {
        labels,

        label:     'Labels',
        type:      'labels',
        param:     'labels',
        modeParam: 'labels_mode'
      };
      filterSelector
        .push(labelsFilter);
    }
    return filterSelector;
  });
