import { createSelector } from 'reselect';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';


export const massActionsSelector = createSelector(
  [
    collectionSelectorFactory('Language', 'all'),
    collectionSelectorFactory('TicketCategory', 'tickets'),
    collectionSelectorFactory('TicketProduct', 'tickets'),
    collectionSelectorFactory('TicketWorkflow', 'tickets'),
    collectionSelectorFactory('Person', 'agents')
  ],
  (languages, categories, products, workflows, agents) => {
    const massActions = [];
    // Status options
    massActions.push({
      label: 'Status',
      type: 'set_action',
      param: 'set_ticket_status',
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
    const productOptions = products.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    const categoryOptions = categories.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    const workflowOptions = workflows.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    const languagesOptions = languages.toArray().map(type => ({ value: type.get('id'), label: type.get('locale') }));

    const setOptions = [
      { label: 'Product', options: productOptions, param: 'set_product', type: 'set_action' },
      { label: 'Category', options: categoryOptions, param: 'set_int_category', type: 'set_action' },
      { label: 'Workflow', options: workflowOptions, param: 'set_workflow', type: 'set_action' },
      { label: 'Language', options: languagesOptions, param: 'set_language', type: 'set_action' }
    ];
    massActions.push({ label: 'Set', type: 'menu', param: 'set_menu', options: setOptions });

    // Followers options
    const followerOptions = agents.toArray().map(type => ({ value: type.get('id'), label: type.get('name') }));

    massActions.push({ label: 'Followers', type: 'select_action', param: 'set_followers', options: followerOptions });

    // Reply options
    massActions.push({ label: 'Reply', type: 'set_action', param: 'reply', options: [] });

    // Other options
    const otherOptions = [
      { label: 'Delete', value: 'delete' },
      { label: 'Mark as Spam', value: 'mark_as_spam' }
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