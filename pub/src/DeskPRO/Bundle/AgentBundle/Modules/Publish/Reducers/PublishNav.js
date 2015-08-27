import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/publishNavActions';

export default class PublishNav extends Reducer {
  getInitialState() {
    return {
      articles: {
        grouped_by: 'category',
        count: 0,
        nested: []
      },
      news: {
        grouped_by: 'category',
        count: 0,
        nested: []
      },
      downloads: {
        grouped_by: 'category',
        count: 0,
        nested: []
      },

      grouping: {
        options: [
          {value: 'category', label: 'Category'},
          {value: 'author', label: 'Author'},
          {value: 'period_created', label: 'Created'},
          {value: 'period_updated', label: 'Updated'},
        ],
        visibility: {
          articles: false,
          news: false,
          downloads: false
        }
      },

      categories: {
        articles:  { /* id: name */ },
        news:      { /* id: name */ },
        downloads: { /* id: name */ }
      },
      authors: {
        /* id: name */
      }
    };
  }

  registerHandlers() {
    this
      .r(actions.loadCounts, this.countsLoaded)
      .r(actions.loadAuthorName, this.authorNameLoaded)
      .r(actions.loadCategories, this.categoriesLoaded)
      .r(actions.toggleListGroupingVisibility, this.listGroupingVisibilityChanged)
    ;
  }

  countsLoaded(prev, {payload}) {
    const next = {...prev};
    next[payload.content] = payload.counts;

    return next;
  }

  authorNameLoaded(prev, {payload}) {
    const next = {...prev};
    next.authors[payload.id] = payload.name;

    return next;
  }

  categoriesLoaded(prev, {payload}) {
    const categories = {articles: {}, news: {}, downloads: {}};
    for (let i = 0; i < payload.articles.length; i++) {
      categories.articles[payload.articles[i].id] = payload.articles[i].title;
    }
    for (let i = 0; i < payload.news.length; i++) {
      categories.news[payload.news[i].id] = payload.news[i].title;
    }
    for (let i = 0; i < payload.downloads.length; i++) {
      categories.downloads[payload.downloads[i].id] = payload.downloads[i].title;
    }

    return {...prev, categories};
  }

  listGroupingVisibilityChanged(prev, {payload}) {
    const next = {...prev};
    next.grouping = Object.assign({}, next.grouping);
    next.grouping.visibility[payload] = !next.grouping.visibility[payload];

    return next;
  }
}
