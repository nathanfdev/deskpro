import DpApi from "../DpApi";

/**
 * @return Promise
 */
export function loadPersonLabels() {
  return DpApi.sendGet('DP_API/person_labels');
}

/**
 * @return Promise
 */
export function loadOrganizationLabels() {
  return DpApi.sendGet('DP_API/organization_labels');
}
