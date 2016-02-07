import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

export function load(options) {
  console.log('DP_API/organizations?' + compileParams(options));
  return api.sendGet('DP_API/organizations?' + compileParams(options));
}
