import { api } from '../DpApi';
import { compileParams } from '../ApiHelpers';

export function load(options) {
  console.log('DP_API/organizations?' + compileParams(options));
  return api.sendGet('DP_API/organizations?' + compileParams(options));
}
