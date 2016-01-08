import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { userGroupsSelector } from '../RecordStores/Selectors/userGroupsSelectors';
import { organizationsSelector } from './recordStores';

const stateSelector = state => state.CRM.list;

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);

export const elementsSelector = createSelector(
  stateSelector,
    state => state.get('elements')
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
  [currentContentSelector, userGroupsSelector, organizationsSelector],
  (currentContent, userGroups, organizations) => {
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
      { label: 'Created', type: 'singleSelect', param: 'period_created', options: datePeriodsOptions() }
    ];
    if (currentContent === 'people') {
      const userGroupsOptions = userGroups.toArray()
        .map(group => ({
          label: group.get('title'),
          value: group.get('id')
        })
      );
      filterSelector.push({
        label: 'User group', type: 'select', param: 'user_group', quickFilter: true,
        options: userGroupsOptions
      });
      const organizationsOptions = organizations.toArray().map(org=>({ label: org.get('name'), value: org.get('id') }));

      console.log('options  ', organizationsOptions);
      const compare = (a, b) => {
        if (a.label < b.label) {
          return -1;
        } else if (a.label > b.label) {
          return 1;
        }
        return 0;
      };

      organizationsOptions.sort(compare);
      filterSelector.push({
        label: 'Organization', type: 'select', param: 'organization', quickFilter: true,
        options: organizationsOptions
      });
    }

    return filterSelector;
  }
);