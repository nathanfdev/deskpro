const portal_window = {
  base_url: window.DESKPRO_BASE_URL,
  root_url: window.DESKPRO_ROOT_URL,
  web_url: window.DESKPRO_WEB_URL,
  lang: window.DESKPRO_LANG,
  is_multi_lang: window.DESKPRO_MULTI_LANG,
  enabled_langs: window.DESKPRO_ENABLED_LANGS
};

console.log('PortalWindow: %o', portal_window);

export default portal_window;
