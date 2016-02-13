import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { ContentRepository } from './ContentRepository';

/**
 * CommentsRepository
 */
export class CommentsRepository {
  /**
   * @param api
   */
  constructor(api) {
    this.api = api;
  }

  /**
   * @param target
   * @param filters
   * @return Promise
   */
  load(target, filters) {
    return this.api.sendGet(
      getEndpoint(ContentRepository.validateTarget(target)) + '?' + compileParams(filters)
    );
  }

  /**
   * @param target
   * @return Promise
   */
  loadCommentsToValidateCounts(target) {
    return this.api.sendGet(
      getCountsEndpoint(ContentRepository.validateTarget(target)) + '?group_by=period_created&status=validating'
    );
  }

  /**
   * @param target
   * @return Promise
   */
  loadCommentsToReviewCount(target) {
    return this.api.sendGet(
      getCountsEndpoint(ContentRepository.validateTarget(target)) + '?is_reviewed=1'
    );
  }
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