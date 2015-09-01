import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/publishListActions';

export default class PublishList extends Reducer {
  getInitialState() {
    return {
      view: 'table',
      content: 'articles', // articles, news, downloads, todo
      articles: [],
      news: [],
      downloads: []
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
    return {...prev, view: prev.view === 'table' ? 'list' : 'table'};
  }
}
