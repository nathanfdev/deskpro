import { createReducer } from 'Ampliflux';
import { async, setFullPayload, mergeFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/publishListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  async: {
    done: true
  },
  // view mode (table or list)
  view: constants.VIEW_MODE_TABLE,

  // which list is displayed
  content: 'articles',

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
      state.set(payload.content, payload.elements)
  }),
  [actions.switchContent]: (state, payload) => state.set('content', payload),
  [actions.toggleView]: async({
    success: (state, payload) => state.set('view', constants.VIEW_MODE_CARD)
  }),
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
