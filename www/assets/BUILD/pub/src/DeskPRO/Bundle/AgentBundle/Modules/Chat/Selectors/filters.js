import { createSelector } from 'reselect';
import { currentListParamsSelector } from './list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

const navStateSelector = state => state.Feedback.nav;

export const listFiltersSelector = createSelector(
  [
    collectionSelectorFactory('Department', 'all_chat')
  ],
  (departments) => {
    const departmentOptions = departments.toArray()
      .map(department => ({ value: department.get('id'), label: department.get('title') }));

    const filterSelector = [
      { label: 'Date Created', type: 'datePeriod', param: 'date_created', property: 'date_created' },
      { label: 'Department', type: 'select', param: 'department', options: departmentOptions }
    ];
    return filterSelector;
  }
);
