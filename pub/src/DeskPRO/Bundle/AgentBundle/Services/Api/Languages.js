import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export function loadLanguages() {
  return api.sendGet('DP_API/languages');
}

export function loadLanguage(lang_id) {
  return api.sendGet(`DP_API/languages/${lang_id}`);
}
