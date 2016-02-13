import DpApi from "../DpApi";

/** Load all departments. */
export function loadLanguages() {
    return DpApi.sendGet('DP_API/languages');
}

export function loadLanguage(lang_id) {
    return DpApi.sendGet(`DP_API/languages/${lang_id}`);
}
