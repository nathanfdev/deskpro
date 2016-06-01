import { createSelector } from 'reselect';
import { currentListParamsSelector } from './list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

const navStateSelector = state => state.Feedback.nav;

export const listFiltersSelector = createSelector(
  [currentListParamsSelector, collectionSelectorFactory('Department', 'all_chat')],
  (currentListParams, departments) => {
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
    return filterSelector;
  }
);
