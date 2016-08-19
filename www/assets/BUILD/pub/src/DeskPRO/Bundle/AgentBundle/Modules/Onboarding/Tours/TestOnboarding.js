class TestOnboarding {
  static get() {
    return {
      force: false,

      steps: [
        {
          title:    'Trigger Action',
          text:     'Test Onboarding',
          selector: '#react_dp_agent_top_bar .item.add',
          position: 'bottom'
        },
        {
          title:    'Notifications',
          text:     'Here are now the notifications',
          selector: '#notifications',
          position: 'bottom'
        },
        {
          title:    'Chat',
          text:     'Your chat login settings and volume are now here in the top right corner',
          selector: '#react_dp_agent_top_bar .chat',
          position: 'left'
        }
      ]
    };
  }
}
export default TestOnboarding;
