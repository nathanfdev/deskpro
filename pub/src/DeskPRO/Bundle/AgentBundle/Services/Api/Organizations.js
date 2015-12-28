import DpApi from '../DpApi';

export function load() {
  console.log('DP_API/organizations');
  return DpApi.sendGet('DP_API/organizations');
}
