import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async, togglePayloadInCollection, handleMassAction, pushPayloadToCollection } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/listActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  listParams: {
    nav: null,
    filters: {
      label_mode: 'any'
    }
  },
  visibleFields: {
    [constants.VIEW_MODE_CARD]: ['title', 'project', 'date_due', 'assignee'],
    [constants.VIEW_MODE_TABLE]: ['id', 'project', 'date_due', 'assignee'],
    [constants.VIEW_MODE_KANBAN]: ['title', 'project', 'date_due', 'assignee'],
    [constants.VIEW_MODE_CALENDAR]: ['title', 'project', 'date_due', 'assignee']
  },
  elements: {},
  selected: [],
  view: 'card',
  async: {
    done: null
  }
};

export default createReducer(initialState, {
  [actions.setListParamsNav]: setFullPayload('listParams.nav'),
  [actions.setListParamsFilters]: setFullPayload('listParams.filters'),
  [actions.toggleCardFieldVisibility]: togglePayloadInCollection(['visibleFields', constants.VIEW_MODE_CARD]),
  [actions.toggleTableFieldVisibility]: togglePayloadInCollection(['visibleFields', constants.VIEW_MODE_TABLE]),
  [actions.toggleKanbanFieldVisibility]: togglePayloadInCollection(['visibleFields', constants.VIEW_MODE_KANBAN]),
  [actions.toggleCalendarFieldVisibility]: togglePayloadInCollection(['visibleFields', constants.VIEW_MODE_CALENDAR]),
  [actions.toggleSelected]: togglePayloadInCollection('selected'),
  [actions.toggleAll]: handleMassAction('elements', 'selected'),
  [actions.unload]: setValue('elements', []),
  [actions.loadList]: async({
    success: (state, payload) => {
      return state
        .set('elements', Immutable.fromJS(payload.ids))
        .set('pagination', Immutable.fromJS(payload.pagination));
    },
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [actions.editTask]: async({
    success: setFullPayload('elements')
  }),
  [actions.addTask]: async({
    success: pushPayloadToCollection('elements')
  })
});
