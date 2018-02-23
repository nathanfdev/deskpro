import React from 'react';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { storiesOf } from '@storybook/react';
import { adminCss } from '../../../decorators';

storiesOf('Admin: Common', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Loading screen',
    () => <LoadingPage />
  )
;
