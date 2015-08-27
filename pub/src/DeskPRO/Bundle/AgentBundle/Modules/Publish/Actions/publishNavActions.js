import { createAction } from 'Ampliflux/actions';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';

export const loadCounts = createAction(
  'PUBLISH_NAV_LOAD_CONTENT_COUNTS',
  (trigger, content, groupBy) => Content.loadCounts(content, groupBy).then(promise => {
    const counts = promise.getData().data;
    trigger({content, counts});

    switch (groupBy) {
      case 'category':
        return loadCategories();

      case 'author':
        counts.nested.forEach(count => trigger(loadAuthorName(count.group)));
        return;

      case 'period_created':
      case 'period_updated':
        return;

      default:
        throw 'Unknown grouping by ' + groupBy;
    }
  })
);

export const loadAuthorName = createAction(
  'PUBLISH_NAV_LOAD_AUTHOR_NAME',
  (trigger, id) => People.loadPerson(id).then(promise => trigger({id, name: promise.getData().data.name}))
);

export const loadCategories = createAction(
  'PUBLISH_NAV_LOAD_CATEGORIES',
  trigger => Content.loadCategories().then(promise => trigger(promise.getData().data))
);

export const toggleListGroupingVisibility = createAction(
  'PUBLISH_NAV_TOGGLE_LIST_GROUPING_VISIBILITY',
  (trigger, list) => trigger(list)
);

export const changeListGrouping = createAction(
  'PUBLISH_NAV_CHANGE_LIST_GROUPING',
  (trigger, list, groupBy) => {
    trigger(loadCounts(list, groupBy));
    trigger(toggleListGroupingVisibility(list));
  }
);