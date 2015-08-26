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

      categories: {
        articles:  { /* id: name */ },
        news:      { /* id: name */ },
        downloads: { /* id: name */ }
      }
    };
  }

  registerHandlers() {
    this
      .r(actions.loadArticlesCounts, this.articlesCountsLoaded)
      .r(actions.loadNewsCounts, this.newsCountsLoaded)
      .r(actions.loadDownloadsCounts, this.downloadsCountsLoaded)
      .r(actions.loadCategories, this.categoriesLoaded)
    ;
  }

  articlesCountsLoaded(prev, {payload}) {
    return {...prev, articles: payload};
  }
  newsCountsLoaded(prev, {payload}) {
    return {...prev, news: payload};
  }
  downloadsCountsLoaded(prev, {payload}) {
    return {...prev, downloads: payload};
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
}
