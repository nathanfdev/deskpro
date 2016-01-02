const portalWindow = {
  base_url: window.DESKPRO_BASE_URL,
  root_url: window.DESKPRO_ROOT_URL,
  web_url: window.DESKPRO_WEB_URL,
  lang: window.DESKPRO_LANG,
  is_multi_lang: window.DESKPRO_MULTI_LANG,
  enabled_langs: window.DESKPRO_ENABLED_LANGS,
  can_use_tickets: window.DESKPRO_CAN_USE_TICKETS,
  can_use_feedback: window.DESKPRO_CAN_USE_FEEDBACK,
  can_use_chat: window.DESKPRO_CAN_USE_CHAT
};

console.log('PortalWindow: %o', portalWindow);

export default portalWindow;
