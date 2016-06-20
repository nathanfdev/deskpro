import { createReducer } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { async, setValue, mergeFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/publishNavActions';

const initialState = {
  async: { done: false },

  articles: {
    grouped_by: 'category',
    count:      0,
    nested:     []
  },

  news: {
    grouped_by: 'category',
    count:      0,
    nested:     []
  },

  downloads: {
    grouped_by: 'category',
    count:      0,
    nested:     []
  },

  todo: {
    articles: {
      draft:   0,
      pending: 0,
      mine:    true
    },

    comments: {
      validate: {},
      review:   {}
    }
  },

  // Lists grouping control popup data
  grouping: {
    options: [
      { value: 'category', label: 'Category' },
      { value: 'author', label: 'Author' },
      { value: 'period_created', label: 'Created' },
      { value: 'period_updated', label: 'Updated' }
    ],

    visibility: {
      articles:  false,
      news:      false,
      downloads: false
    }
  },

  // List labels
  groups: {
    categories: {
      articles:  { /* id: name */ },
      news:      { /* id: name */ },
      downloads: { /* id: name */ }
    },

    authors: { /* id: name */ }
  }
};
export default createReducer(initialState, {
  [actions.loadCommentsToValidateCounts]: async({
    success: (state, payload) =>
               state.setIn(['todo', 'comments', 'validate'], payload)
  }),

  [actions.loadCounts]: async({
    success: (state, payload) => state.set(payload.content, Immutable.fromJS(payload.counts)),
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  }),

  [actions.loadCategories]: async({
    success: (state, payload) => state.setIn([payload.content], payload.counts)
  }),

  [actions.loadPendingCount]: async({
    success: (state, payload) => state.setIn(['todo', 'articles', 'pending'], payload)
  }),

  [actions.loadDraftsCount]: async({
    success: (state, payload) => state.setIn(['todo', 'articles', 'draft'], payload)
  }),

  [actions.loadCommentsToReviewCount]: async({
    success: (state, payload) => state.setIn(['todo', 'comments', 'review'], payload),
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  }),

  [actions.changeListGrouping]: (state, payload) => state.setIn([payload.content, 'grouped_by'], payload.grouped_by),

  [actions.setMine]: (state, payload) => state.setIn(['todo', 'articles', 'mine'], payload),

  [actions.initialLoad]: async({
    success: mergeFullPayload(),
    start:   setValue('async.done', false),
    done:    setValue('async.done', true)
  })
});
