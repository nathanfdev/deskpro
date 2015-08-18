import DpApi from "../DpApi";

/**
 * Get organizations total count
 * @return Promise
 */
export function loadCount() {
  return DpApi.sendGet('DP_API/organizations/counts');
}