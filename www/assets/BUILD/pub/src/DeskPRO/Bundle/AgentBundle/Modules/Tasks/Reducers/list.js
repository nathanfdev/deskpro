import { createReducer } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import {
  setFullPayload, setValue, async, togglePayloadInCollection, handleMassAction, pushPayloadToCollection
}
  from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/listActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import invariant from 'invariant';

const initialState = {
  elements:   [],
  selected:   [],
  view:       'card',
  async:      { done: null },
  listParams: {
    nav:     null,
    filters: { label_mode: 'any' }
  },

  fields: {
    [constants.VIEW_MODE_CARD]: [
      { id: 'id', title: 'Id', visible: true, required: true },
      { id: 'title', title: 'Title', visible: true, required: true },
      { id: 'project', title: 'Project', visible: true, required: true },
      { id: 'date_due', title: 'Due', visible: true },
      { id: 'linked', title: 'Linked Items', visible: true },
      { id: 'assignee', title: 'Assignee', visible: true }
    ],

    [constants.VIEW_MODE_TABLE]: [
      { id: 'id', title: 'Id', visible: true },
      { id: 'title', title: 'Title', visible: true },
      { id: 'project', title: 'Project', visible: true },
      { id: 'date_due', title: 'Due', visible: true },
      { id: 'assignee', title: 'Assignee', visible: true }
    ],

    [constants.VIEW_MODE_KANBAN]: [
      { id: 'date_due', title: 'Due', visible: true },
      { id: 'project', title: 'Project', visible: true },
      { id: 'linked', title: 'Linked Items', visible: true },
      { id: 'assignee', title: 'Assignee', visible: true }
    ],

    [constants.VIEW_MODE_CALENDAR]: [
      { id: 'project', title: 'Project', visible: true },
      { id: 'date_due', title: 'Due', visible: true },
      { id: 'assignee', title: 'Assignee', visible: true }
    ]
  }
};

export default createReducer(initialState, {
  [actions.loadIndicator]:         setValue('async.done', false),
  [actions.setListParamsNav]:      setFullPayload('listParams.nav'),
  [actions.setListParamsFilters]:  setFullPayload('listParams.filters'),
  [actions.toggleFieldVisibility]: (state, { type, index }) => {
    const old = state.getIn(['fields', type, index, 'visible']);
    if (undefined === old) return state;
    return state.setIn(['fields', type, index, 'visible'], !old);
  },

  [actions.changeFieldOrder]: (state, { type, from, to }) => {
    const fromField = state.getIn(['fields', type, from]);
    const toField   = state.getIn(['fields', type, to]);
    if (undefined === fromField || undefined === toField) return state;
    return state
      .setIn(['fields', type, from], toField)
      .setIn(['fields', type, to], fromField);
  },

  [actions.toggleSelected]: togglePayloadInCollection('selected'),

  [actions.toggleAll]: handleMassAction('elements', 'selected'),

  [actions.loadList]: async(
    {
      success: (state, payload) => {
        invariant(
          Array.isArray(payload.ids),
          'Reducer actions.loadList expects payload.ids to be an Array. Got %s',
          payload.ids
        );
        invariant(
          typeof payload.pagination === 'object',
          'Reducer actions.loadList expects payload.pagination to be an Object. Got %s',
          payload.pagination
        );
        return state
          .set('elements', Immutable.fromJS(payload.ids))
          .set('pagination', Immutable.fromJS(payload.pagination));
      },

      start: setValue('async.done', false),
      done:  setValue('async.done', true)
    }),

  [actions.addTask]: async({ success: pushPayloadToCollection('elements') })
});
