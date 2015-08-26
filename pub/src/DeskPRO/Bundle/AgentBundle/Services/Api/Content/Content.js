import DpApi from "../../DpApi";

/**
 * @param groupBy
 * @return Promise
 */
export function loadArticlesCounts(groupBy) {
  return DpApi.sendGet('DP_API/articles/counts?group_by=' + groupBy);
}

/**
 * @param groupBy
 * @return Promise
 */
export function loadNewsCounts(groupBy) {
  return DpApi.sendGet('DP_API/news/counts?group_by=' + groupBy);
}

/**
 * @param groupBy
 * @return Promise
 */
export function loadDownloadsCounts(groupBy) {
  return DpApi.sendGet('DP_API/downloads/counts?group_by=' + groupBy);
}

/**
 * @return Promise
 */
export function loadCategories() {
  return DpApi.sendGet('DP_API/content_categories');
}