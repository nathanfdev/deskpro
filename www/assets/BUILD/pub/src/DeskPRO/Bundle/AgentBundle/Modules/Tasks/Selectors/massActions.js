import { createSelector } from 'reselect';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';

export const massActionsSelector = createSelector(
  [collectionSelectorFactory('Project', 'all'), agentsSelector],
  (projects, agents) => {
    const massActions = [];
    // Project options
    const projectOptions = projects.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    massActions.push({
      label: 'Project',
      type: 'set_action',
      param: 'set_project',
      quickFilter: true,
      options: projectOptions
    });

    // Assign options
    const assignOptions = agents.toArray().map(type => ({ value: type.get('id'), label: type.get('name') }));
    massActions.push({
      label: 'Assign',
      type: 'assign_action',
      param: 'assign',
      quickFilter: true,
      options: assignOptions
    });

    // Due date options
    massActions.push({
      label: 'Due date',
      type: 'set_date',
      param: 'set_due_date'
    });

    // Status options
    massActions.push({
      label: 'Status', type: 'set_action', param: 'set_status',
      options: [{ value: 1, label: 'Complete' }, { value: 0, label: 'Incomplete' }]
    });

    // Other options
    const otherOptions = [
      { label: 'Delete', param: 'delete' }
    ];
    massActions.push({
      icon: 'fa-asterisk',
      type: 'select_action',
      param: 'other',
      options: otherOptions
    });

    return massActions;
  }
);