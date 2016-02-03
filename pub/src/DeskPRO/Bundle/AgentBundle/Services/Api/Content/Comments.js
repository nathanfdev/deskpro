import { api } from '../../DpApi';
import { compileParams } from '../../ApiHelpers';
import { validateTarget } from './Content';

/**
 * @param target
 * @param filters
 * @return Promise
 */
export function load(target, filters) {
  return api.sendGet(
    getEndpoint(validateTarget(target)) + '?' + compileParams(filters)
  );
}

/**
 * @param target
 * @return Promise
 */
export function loadCommentsToValidateCounts(target) {
  return api.sendGet(
    getCountsEndpoint(validateTarget(target)) + '?group_by=period_created&status=validating'
  );
}

/**
 * @param target
 * @return Promise
 */
export function loadCommentsToReviewCount(target) {
  return api.sendGet(
    getCountsEndpoint(validateTarget(target)) + '?is_reviewed=1'
  );
}

/**
 * Get API endpoint for comment counts of given content target (articles, news, downloads)
 * @param target
 * @return {string}
 */
function getCountsEndpoint(target) {
  return getEndpoint(target) + '/counts'
}

/**
 * Get API endpoint for comments of given content target (articles, news, downloads)
 * @param target
 * @return {string}
 */
function getEndpoint(target) {
  switch (target) {
    case 'articles':
      return 'DP_API/article_comments';
    case 'news':
      return 'DP_API/news_comments';
    case 'downloads':
      return 'DP_API/download_comments';
  }
}