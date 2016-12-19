const topbar = {
  force: true,
  steps: [
    {
      title:    'agent.onboarding.topbar_search_title',
      text:     'agent.onboarding.topbar_search_text',
      selector: '#react_dp_agent_top_bar .item.search-box',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.topbar_history_title',
      text:     'agent.onboarding.topbar_history_text',
      selector: '#react_dp_agent_top_bar .item.recent',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.topbar_create_title',
      text:     'agent.onboarding.topbar_create_text',
      selector: '#react_dp_agent_top_bar .item.add',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.topbar_views_title',
      text:     'agent.onboarding.topbar_views_text',
      selector: '#react_dp_agent_top_bar .item.views',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.topbar_notifications_title',
      text:     'agent.onboarding.topbar_notifications_text',
      selector: '#react_dp_agent_top_bar .item.notifications',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.topbar_chat_title',
      text:     'agent.onboarding.topbar_chat_text',
      selector: '#react_dp_agent_top_bar .chat',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.topbar_profile_title',
      text:     'agent.onboarding.topbar_profile_text',
      selector: '#react_dp_agent_top_bar .user',
      position: 'bottom'
    },
  ],
  intro: {
    title:  'agent.onboarding.topbar_intro_title',
    text:   'agent.onboarding.topbar_intro_text',
    action: 'agent.onboarding.topbar_intro_button',
    img:    `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/onboarding/topbar_intro.png`
  }
};
export default topbar;

