import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { userGroupsSelector } from '../RecordStores/Selectors/userGroupsSelectors';

const stateSelector = state => state.CRM.list;

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);

export const organizationsSelector = createSelector(
  stateSelector,
    state => state.get('organizations')
);

export const peopleSelector = createSelector(
  stateSelector,
    state => state.get('people')
);

export const currentListSortSelector = createSelector(
  currentListParamsSelector,
    params => params.get('sort')
);

export const currentListOrderSelector = createSelector(
  currentListParamsSelector,
    params => params.get('order')
);

export const currentContentSelector = createSelector(
  currentListParamsSelector,
    params => params.get('content')
);

export const listFiltersSelector = createSelector(
  [currentListParamsSelector, userGroupsSelector],
  (currentListParams, userGroups) => {
    const datePeriodsOptions = () => {
      const periods = DatePeriods.all;
      const options = [];
      for (var property in periods) {
        if (periods.hasOwnProperty(property)) {
          options.push({ value: property, label: periods[property] });
        }
      }
      return options;
    };
    const filterSelector = [
      { label: 'Created', type: 'singleSelect', param: 'date_created', options: datePeriodsOptions() }
    ];
    if (currentListParams.get('content') === 'people') {
      const userGroupsOptions = userGroups.toArray()
        .map(group => ({
          label: group.get('title'),
          value: group.get('id')
        })
      );
      filterSelector.push({
        label: 'User group', type: 'singleSelect', param: 'usergroups', quickFilter: true,
        options: userGroupsOptions
      });
    }

    return filterSelector;
  }
);