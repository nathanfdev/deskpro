import { createReducer } from 'Ampliflux';
import { async, setFullPayload, togglePayloadInCollection,handleMassAction } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/publishListActions';
import * as massActions from '../Actions/publishMassActions.js';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  async: {
    done: true
  },
  selected: [],
  // view mode (table or list)
  view: constants.VIEW_MODE_CARD,
  // currently viewed list GET parameters map
  currentListParams: {
    sort: 'date_created',
    order: constants.ORDER_DESC,
    // which list is displayed
    content: 'articles'
  },

  // lists
  articles: [],
  news: [],
  downloads: [],
  draftArticles: [],
  pendingArticles: [],
  commentsToValidate: [],
  commentsToReview: []
};

export default createReducer(initialState, {
  [actions.load]: async({
    success: (state, payload) =>
      state.set(payload.content, payload.data.data).set('pagination', payload.data.meta.pagination)
  }),
  [actions.setParams]: setFullPayload('currentListParams'),
  [massActions.toggleMassAction]: handleMassAction('elements', 'selected'),
  [massActions.toggleSelectedAction]: togglePayloadInCollection('selected')
});

/*

 export default class PublishList extends Reducer {

 registerHandlers() {
 this
 .r(actions.load, this.contentLoaded)
 .r(actions.switchContent, this.contentSwitched)
 .r(actions.toggleView, this.viewToggled)
 ;
 }

 contentLoaded(prev, {payload}) {
 const next = { ...prev };
 next[payload.content] = payload.elements;

 return next;
 }

 contentSwitched(prev, {payload}) {
 return { ...prev, content: payload };
 }

 viewToggled(prev) {
 return {
 ...prev,
 view: prev.view === constants.VIEW_MODE_CARD ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_CARD
 };
 }
 }*/
