import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/publishListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export default class PublishList extends Reducer {
  getInitialState() {
    return {

      // view mode (table or list)
      view: 'table',

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
  }

  registerHandlers() {
    this
      .r(actions.load, this.contentLoaded)
      .r(actions.switchContent, this.contentSwitched)
      .r(actions.toggleView, this.viewToggled)
    ;
  }

  contentLoaded(prev, {payload}) {
    const next = {...prev};
    next[payload.content] = payload.elements;

    return next;
  }

  contentSwitched(prev, {payload}) {
    return {...prev, content: payload};
  }

  viewToggled(prev) {
    return {...prev, view: prev.view === constants.VIEW_MODE_CARD ? constants.VIEW_MODE_TABLE : constants.VIEW_MODE_CARD};
  }
}