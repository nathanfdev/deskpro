import { createSelector } from 'reselect';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';


export const massActionsSelector = createSelector(
  [
    collectionSelectorFactory('Language', 'all'),
    collectionSelectorFactory('TicketCategory', 'tickets'),
    collectionSelectorFactory('TicketProduct', 'tickets'),
    collectionSelectorFactory('TicketWorkflow', 'tickets')
  ],
  (languages, categories, products, workflows) => {
    const massActions = [];
    // Status options
    massActions.push({
      label: 'Status',
      type: 'set_action',
      param: 'set_status',
      quickFilter: true,
      options: [
        { value: 'awaiting_agent', label: 'Awaiting agent' },
        { value: 'awaiting_user', label: 'Awaiting user' },
        { value: 'resolved', label: 'Resolved' },
        { value: 'archived', label: 'Archived' }
      ]
    });

    // Assign options
    massActions.push({
      label: 'Assign',
      type: 'assign_action',
      param: 'assign',
      quickFilter: true
    });

    // Set options
    const otherOptions = [
      { label: 'Product', options: products, param: 'set_product', type: 'set_action' },
      { label: 'Category', options: categories, param: 'set_category', type: 'set_action' },
      { label: 'Workflow', options: workflows, param: 'set_workflow', type: 'set_action' },
      { label: 'Language', options: languages, param: 'set_language', type: 'set_action' }
    ];
    massActions.push({ label: 'Set', type: 'menu', param: 'other', options: otherOptions });

    return massActions;
  }
);