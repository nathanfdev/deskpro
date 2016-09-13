import introPng from 'DeskPRO/Bundle/AgentBundle/Resources/img/onboarding/topbar_intro.png';

const topbar = {
  force: true,
  steps: [
    {
      title:    'Search',
      text:     'You can search the helpdesk for tickets, people, articles etc. in the top-left corner of the screen.',
      selector: '#react_dp_agent_top_bar .item.search-box',
      position: 'bottom'
    },
    {
      title:    'History',
      text:     'Quickly re-open items you have recently viewed.',
      selector: '#react_dp_agent_top_bar .item.recent',
      position: 'bottom'
    },
    {
      title: 'Create',
      text:  'Add new tickets, people, articles etc. to your ' +
        'helpdesk by clicking the \'create button\' in the header.',
      selector: '#react_dp_agent_top_bar .item.add',
      position: 'bottom'
    },
    {
      title:    'Manage views',
      text:     'Click the \'views icon\' when you want to change the layout of your screen.',
      selector: '#react_dp_agent_top_bar .item.views',
      position: 'bottom'
    },
    {
      title:    'Notifications',
      text:     'You will receive notifications about helpdesk activity (e.g. new tickets).',
      selector: '#react_dp_agent_top_bar .item.notifications',
      position: 'bottom'
    },
    {
      title:    'Your profile',
      text:     'Manage your preferences, find help and log out of DeskPRO.',
      selector: '#react_dp_agent_top_bar .user',
      position: 'bottom'
    },
    {
      title:    'Chat',
      text:     'You can control whether you are online to answer user chats.',
      selector: '#react_dp_agent_top_bar .chat',
      position: 'bottom'
    },
  ],
  intro: {
    title:  'New DeskPRO update',
    text:   'We’ve made a few changes to how you navigate DeskPRO. Let’s take a quick look...',
    action: 'Start',
    img:    introPng
  }
};
export default topbar;

