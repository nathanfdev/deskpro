const newIm = {
  force:   true,
  waitFor: '#im-button',
  steps:   [
    {
      title:    'agent.onboarding.new_im_position_title',
      text:     'agent.onboarding.new_im_position_text',
      selector: '#im-button',
      position: 'bottom'
    },
    {
      title:    'agent.onboarding.new_im_start_new_title',
      text:     'agent.onboarding.new_im_start_new_text',
      selector: '.im.wrapper #im-button',
      position: 'bottom'
    }
  ]
};

export default newIm;

