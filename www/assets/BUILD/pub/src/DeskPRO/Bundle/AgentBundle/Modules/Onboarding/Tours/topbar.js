const topbar = {
  force: false,
  steps: [
    {
      title: 'Search and recent tabs',
      text: 'You can search the helpdesk and view recent tabs in the top-left corner of the screen.',
      selector: '#react_dp_agent_top_bar .item.search-box',
      position: 'bottom'
    },
    {
      title: 'Search and recent tabs',
      text: 'You can search the helpdesk and view recent tabs in the top-left corner of the screen.',
      selector: '#react_dp_agent_top_bar .item.recent',
      position: 'bottom'
    },
    {
      title: 'Create',
      text: 'Add new tickets, articles, users (and more) to your helpdesk by clicking the \'create button\' in the header.',
      selector: '#react_dp_agent_top_bar .item.add',
      position: 'bottom'
    },
    {
      title: 'Manage views',
      text: 'Click the views icon when you want to change the layout of your screen.',
      selector: '#react_dp_agent_top_bar .item.view_mode',
      position: 'bottom'
    },
    {
      title: 'Notifications',
      text: 'You can access notifications about activity in your helpdesk by clicking here.',
      selector: '#react_dp_agent_top_bar .item.notifications',
      position: 'bottom'
    },
    {
      title: 'Your profile',
      text: 'Manage your preferences, find help and log out of DeskPRO from here.',
      selector: '#react_dp_agent_top_bar .user',
      position: 'bottom'
    },
    {
      title: 'Chat',
      text: 'Change your chat status or settings and see who\'s online by clicking here.',
      selector: '#react_dp_agent_top_bar .chat',
      position: 'bottom'
    },
  ]
};
export default topbar;