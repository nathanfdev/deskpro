import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { createSelector } from 'reselect';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';

const stateSelector = state => state.Tasks.list;

export const currentNavSelector = hashStateSelectorFactory(['nav', 'active']);
export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
export const currentSortSelector = hashStateSelectorFactory(['list', 'sort'], 'date_created');
export const currentOrderSelector = hashStateSelectorFactory(['list', 'order'], 'desc');

export const listParamsNavSelector = createSelector(
  stateSelector,
    state => state.getIn(['listParams', 'nav'])
);

export const listParamsFiltersSelector = createSelector(
  stateSelector,
    state => state.getIn(['listParams', 'filters'])
);

export const tableVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.getIn(['visibleFields', constants.VIEW_MODE_TABLE])
);

export const cardVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.getIn(['visibleFields', constants.VIEW_MODE_CARD])
);

export const kanbanVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.getIn(['visibleFields', constants.VIEW_MODE_KANBAN])
);

export const calendarVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.getIn(['visibleFields', constants.VIEW_MODE_CALENDAR])
);

export const elementsSelector = createSelector(
  stateSelector,
    state => state.get('elements')
);

export const isLoadedSelector = createSelector(
  stateSelector,
    state => state.getIn(['async', 'done'])
);

/* ==================== Mass actions ===================== */

export const massActionsSelector = createSelector(
  [collectionSelectorFactory('Project', 'all'), agentsSelector],
  (projects, agents) => {
    const massActions = [];
    // Project options
    const projectOptions = projects.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    massActions.push({
      label: 'Project',
      type: 'action',
      param: 'set_project',
      quickFilter: true,
      options: projectOptions
    });

    // Assign options
    const assignOptions = agents.toArray().map(type => ({ value: type.get('id'), label: type.get('name') }));
    massActions.push({
      label: 'Assign',
      type: 'action',
      param: 'assign',
      quickFilter: true,
      options: assignOptions
    });

    // Due date options
    massActions.push({
      label: 'Due date',
      type: 'action',
      param: 'set_due_date',
      quickFilter: true,
      options: []
    });

    // Status options
    massActions.push({
      label: 'Status', type: 'action', param: 'set_status', quickFilter: true,
      options: [{ value: 1, label: 'Complete' }, { value: 0, label: 'Incomplete' }]
    });

    // Other options
    const otherOptions = [
      { label: 'Delete', icon: 'minus-square', param: 'delete' }
    ];
    massActions.push({ icon: 'fa-asterisk', type: 'action', param: 'other', options: otherOptions });

    return massActions;
  }
);
