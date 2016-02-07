import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

export function loadPeople(options) {
  if (options.is_me) {
    options.is_me = 1;
  }
  if (options.is_agent) {
    options.is_agent = 1;
  }

  const request = Object.keys(options).length > 0 ? ('&' + compileParams(options)) : '';
  console.log(`DP_API/people?include=organization,usergroup,language${request}`);
  return api.sendGet(`DP_API/people?include=organization,usergroup,language${request}`);
}

export function loadPerson(id) {
  return api.sendGet(`DP_API/people/${id}`);
}
