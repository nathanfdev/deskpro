import React from 'react';
import AudioWidget from 'DeskPRO/Bundle/AdminBundle/Modules/Twilio/Components/Common/AudioWidget/AudioWidget';
import { storiesOf } from '@kadira/storybook';
import { adminCss, redux } from '../../../decorators';

const onSubmit = (data) => {
  console.log(data);
};

storiesOf('Admin: Twilio', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Audio widget',
    () => redux({}, <AudioWidget onSubmit={onSubmit} />)
  )
;
