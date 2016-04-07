import { createSelector } from 'reselect';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { feedbackLabelsSelector } from './nav';

const navStateSelector = state => state.Feedback.nav;

export const massActionsSelector = createSelector(
  [
    navStateSelector, collectionSelectorFactory('FeedbackCategory', 'feedback'),
    collectionSelectorFactory('FeedbackType', 'feedback'), feedbackLabelsSelector
  ],
  (navState, categories, types, labels) => {
    const massActions = [];
    // Type options
    const typeOptions = types.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    massActions.push({
      label: 'Type',
      type: 'set_action',
      param: 'set_type',
      quickFilter: true,
      options: typeOptions
    });

    // Status options
    const statuses = navState.get('statuses').toJS();
    const toStatusOptions = (nested, param) => (nested || []).map(opt => ({
      value: opt.id,
      label: opt.title,
      param
    }));
    const statusOptions = [
      { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested, 'set_status_category') },
      { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested, 'set_status_category') },
      { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested, 'set_hidden_status') }
    ];
    massActions.push({
      label: 'Status', type: 'set_action', param: 'set_status', quickFilter: true,
      options: statusOptions
    });

    // Category options
    const categoryOptions = categories.toArray().map(cat => ({
      label: cat.get('input'),
      value: cat.get('input')
    }));
    massActions.push({
      label: 'Category', type: 'set_action', param: 'set_category', quickFilter: true,
      options: categoryOptions
    });

    // Other options
    const otherOptions = [
      { label: 'Add label', icon: 'plus-square', labels, param: 'add_labels' },
      { label: 'Remove label', icon: 'minus-square', labels, param: 'remove_labels' }
    ];
    massActions.push({ icon: 'fa-asterisk', type: 'menu', param: 'other', options: otherOptions });

    return massActions;
  }
);