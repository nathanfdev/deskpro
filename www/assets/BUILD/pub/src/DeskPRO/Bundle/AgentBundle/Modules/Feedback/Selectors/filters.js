import { createSelector } from 'reselect';
import { feedbackLabelsSelector } from './nav';
import { currentListParamsSelector } from './list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

const navStateSelector = state => state.Feedback.nav;

export const listFiltersSelector = createSelector(
  [
    navStateSelector, currentListParamsSelector, collectionSelectorFactory('FeedbackCategory', 'feedback'),
    feedbackLabelsSelector, collectionSelectorFactory('FeedbackType', 'feedback')
  ],
  (navState, currentListParams, categories, labels, types) => {
    const checkIfShowStatus = () => {
      const navItem = currentListParams.get('navItem');
      return !navItem || (!navItem.get('status') && !navItem.get('status_category') && !navItem.get('hidden_status'));
    };
    const filterSelector    = [
      { label: 'Date', type: 'date', fromParam: 'created_from', toParam: 'created_to' }
    ];

    // Type options
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('category')) {
      const typeOptions = types.toArray().map(type => ({ value: type.get('title'), label: type.get('title') }));
      filterSelector.push({
        label:       'Type',
        type:        'select',
        param:       'category',
        quickFilter: true,
        options:     typeOptions
      });
    }

    // Status options
    if (checkIfShowStatus()) {
      const statuses        = navState.get('statuses').toJS();
      const toStatusOptions = (nested, param) =>
        (nested || []).map(opt => ({ value: opt.id, label: opt.title, param }));
      const statusOptions   = [
        { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested, 'status_category') },
        { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested, 'status_category') },
        { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested, 'hidden_status') }
      ];
      filterSelector
        .push({ label: 'Status', type: 'select', param: 'status', quickFilter: true, options: statusOptions });
    }

    // Category options
    if (!currentListParams.get('navItem') || (!currentListParams.get('navItem').get('custom_category'))) {
      const categoryOptions = categories.toArray().map(cat => ({
        label: cat.get('input'),
        value: cat.get('input')
      }));
      filterSelector.push({
        label:       'Category',
        type:        'select',
        param:       'custom_category',
        quickFilter: true,
        options:     categoryOptions
      });
    }

    // Labels options
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('label')) {
      filterSelector
        .push({ label: 'Labels', type: 'labels', param: 'label', modeParam: 'labels_mode', labels });
    }
    return filterSelector;
  }
);
