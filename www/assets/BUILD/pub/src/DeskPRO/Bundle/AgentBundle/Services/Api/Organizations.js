import DpApi from '../DpApi';
import { compileParams } from '../ApiHelpers';

export function load(options) {
  console.log('DP_API/organizations?' + compileParams(options));
  return DpApi.sendGet('DP_API/organizations?' + compileParams(options));
}
