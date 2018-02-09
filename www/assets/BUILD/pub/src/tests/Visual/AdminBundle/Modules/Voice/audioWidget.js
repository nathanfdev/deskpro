import React from 'react';
import AudioWidget from 'DeskPRO/Component/AudioWidget/AudioWidget';
import { storiesOf } from '@storybook/react';
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
