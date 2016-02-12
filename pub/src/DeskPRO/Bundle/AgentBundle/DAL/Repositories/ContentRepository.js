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
    const {content} = params;
    const newParams = {...params};
    delete newParams.content;
    const include = () => {
      switch (content) {
        case 'articles':
          return 'article_revision';
          break;
        case 'downloads':
          return 'download_revision';
          break;
        case 'news':
          return 'news_revision';
        case 'article_comments':
          return 'article';
          break;
        case 'download_comments':
          return 'download';
          break;
        case 'news_comments':
          return 'news';
        default:
          return '';
      }
    };

    const url = 'DP_API/' + ContentRepository.validateTarget(content)
              + '?include=person,' + include() + '&' + compileParams(newParams);

    return this.api.sendGet(url);
  }

  /**
   * @param target
   * @param groupBy
   * @return Promise
   */
  loadCounts(target, groupBy) {
    return this.api.sendGet('DP_API/' + ContentRepository.validateTarget(target) + '/counts?group_by=' + groupBy);
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
    return this.api.sendGet(
      'DP_API/' + ContentRepository.validateTarget(target) + '/counts?status=hidden&hidden_status=draft'
      + (author ? '&author=' + author : '')
    );
  }

  /**
   * Validates and returns target content
   *
   * @param target
   * @return {*}
   */
  static validateTarget(target) {
    if (['articles', 'news', 'downloads', 'article_comments', 'news_comments', 'download_comments', 'article_pending_creates'].indexOf(target) === -1) {
      throw 'Unknown content type ' + target;
    }

    return target;
  }
}