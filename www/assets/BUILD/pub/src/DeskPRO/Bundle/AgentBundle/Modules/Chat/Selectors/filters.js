import { createSelector } from 'reselect';
import { currentListParamsSelector } from './list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const listFiltersSelector = createSelector(
  [currentListParamsSelector, collectionSelectorFactory('Department', 'all_chat'), collectionSelectorFactory('Agent', 'all_chat')],
  (currentListParams, departments, agents) => {
    const filterSelector = [];

    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('date_period')) {
      filterSelector.push(
        {
          label:    'Date Created',
          type:     'datePeriod',
          param:    'date_created',
          property: 'date_created'
        });
    }

    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('department')) {
      const departmentOptions = departments.toArray()
        .map(department => ({ value: department.get('id'), label: department.get('title') }));

      filterSelector.push({ label: 'Department', type: 'select', param: 'department', options: departmentOptions });
    }

    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('agent')) {
      const agentOptions = agents.toArray()
        .map(agent => ({ value: agent.get('id'), label: agent.get('name') }));

      filterSelector.push({ label: 'Agent', type: 'select', param: 'agent', options: agentOptions });
    }
    return filterSelector;
  }
);
