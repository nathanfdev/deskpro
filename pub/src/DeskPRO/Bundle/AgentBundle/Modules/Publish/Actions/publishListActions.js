import { createAction } from 'Ampliflux/actions';
import * as Content from 'DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content';

export const load = createAction(
  'PUBLISH_LIST_LOAD_ARTICLES',
  (trigger, content, groupBy, group) => Content.load(content, groupBy, group).then(
    promise => {
      trigger({content, elements: promise.getData().data});
      trigger(switchContent(content));
    }
  )
);

export const switchContent = createAction(
  'PUBLISH_LIST_SWITCH_CONTENT',
  (trigger, content) => trigger(content)
);

export const toggleView = createAction(
  'PUBLISH_LIST_TOGGLE_VIEW'
);
