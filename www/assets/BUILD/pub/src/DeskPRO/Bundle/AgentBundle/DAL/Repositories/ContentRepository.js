import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

export class ContentRepository {
  /**
   * @param api
   */
  constructor(api) {
    this.api = api;
  }

  /**
   * @param params
   * @return Promise
   */
  load(params) {
    const { content } = params;
    const newParams = { ...params };
    const include   = () => {
      switch (content) {
        case 'articles':
          return 'article_revision';
        case 'downloads':
          return 'download_revision';
        case 'news':
          return 'news_revision';
        case 'article_comments':
          return 'article';
        case 'download_comments':
          return 'download';
        case 'news_comments':
          return 'news';
        case 'article_pending_creates':
          return 'assigned_person';
        default:
          return '';
      }
    };

    delete newParams.content;

    const url = `DP_API/${ContentRepository.validateTarget(content)}?include=person,${include()}&${compileParams(newParams)}`;

    return this.api.sendGet(url);
  }

  /**
   * @param target
   * @param groupBy
   * @return Promise
   */
  loadCounts(target, groupBy) {
    return this.api.sendGet(`DP_API/${ContentRepository.validateTarget(target)}/counts?group_by=${groupBy}`);
  }

  /**
   * @return Promise
   */
  loadCategories() {
    return this.api.sendGet('DP_API/content_categories');
  }

  /**
   * @param target
   * @param author
   * @return Promise
   */
  loadDraftsCount(target, author) {
    const url = `DP_API/${ContentRepository.validateTarget(target)}/counts?status=hidden&hidden_status=draft${(author ? `&author=${author}` : '')}`;

    return this.api.sendGet(url);
  }

  /**
   * @param {object} params Additional parameters to request
   * @returns {Promise} promise
   */
  loadCsv(params) {
    const { content } = params;
    const newParams = { ...params };
    delete newParams.content;

    return this.api.sendGet(`DP_API/${ContentRepository.validateTarget(content)}/csv?${compileParams(newParams)}`);
  }

  /**
   * Validates and returns target content
   *
   * @param target
   * @return {*}
   */
  static validateTarget(target) {
    const typeOfContent = [
      'articles', 'news', 'downloads', 'article_comments',
      'news_comments', 'download_comments', 'article_pending_creates'
    ];
    if (typeOfContent.indexOf(target) === -1) {
      throw new Error(`Unknown content type ${target}`);
    }

    return target;
  }
}
