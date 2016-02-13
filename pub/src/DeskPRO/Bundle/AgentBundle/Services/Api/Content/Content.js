import DpApi from '../../DpApi';
import { compileParams } from '../../ApiHelpers';

/**
 * @param params
 * @return Promise
 */
export function load(params) {
  const {content} = params;
  const newParams = { ...params };
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
  console.log('DP_API/' + validateTarget(content) + '?include=person,' + include() + '&' + compileParams(newParams));
  return DpApi.sendGet('DP_API/' + validateTarget(content) + '?include=person,' + include() + '&' + compileParams(newParams));
}

/**
 * @param target
 * @param groupBy
 * @return Promise
 */
export function loadCounts(target, groupBy) {
  console.log('DP_API/' + validateTarget(target) + '/counts?group_by=' + groupBy);
  return DpApi.sendGet('DP_API/' + validateTarget(target) + '/counts?group_by=' + groupBy);
}

/**
 * @return Promise
 */
export function loadCategories() {
  return DpApi.sendGet('DP_API/content_categories');
}

/**
 * @param target
 * @param author
 * @return Promise
 */
export function loadDraftsCount(target, author) {
  return DpApi.sendGet(
    'DP_API/' + validateTarget(target) + '/counts?status=hidden&hidden_status=draft'
    + (author ? '&author=' + author : '')
  );
}

/**
 * Validates and returns target content
 *
 * @param target
 * @return {*}
 */
export function validateTarget(target) {
  if (['articles', 'news', 'downloads', 'article_comments', 'news_comments', 'download_comments', 'article_pending_creates'].indexOf(target) === -1) {
    throw 'Unknown content type ' + target;
  }

  return target;
}