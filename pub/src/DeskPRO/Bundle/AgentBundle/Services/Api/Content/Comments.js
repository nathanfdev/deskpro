import DpApi from '../../DpApi';
import { validateTarget } from './Content';

/**
 * @param target
 * @return Promise
 */
export function loadCommentsToValidateCounts(target) {
  return DpApi.sendGet(
    getEndpoint(validateTarget(target)) + '?group_by=period_created&status=validating'
  );
}

/**
 * @param target
 * @return Promise
 */
export function loadCommentsToReviewCount(target) {
  return DpApi.sendGet(
    getEndpoint(validateTarget(target)) + '?is_reviewed=1'
  );
}

/**
 * Get API endpoint for comments of given content target (articles, news, downloads)
 * @param target
 * @return {string}
 */
function getEndpoint(target) {
  switch (target) {
    case 'articles':
      return 'DP_API/article_comments/counts';
    case 'news':
      return 'DP_API/news_comments/counts';
    case 'downloads':
      return 'DP_API/download_comments/counts';
  }
}