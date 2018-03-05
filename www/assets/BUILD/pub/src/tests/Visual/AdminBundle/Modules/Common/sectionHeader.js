import React from 'react';
import SectionHeader from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/SectionHeader';
import { storiesOf } from '@storybook/react';
import { adminCss } from '../../../decorators';

storiesOf('Admin: Common', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Header',
    () => <SectionHeader title="Header" />
  )
  .add(
    'Header with underline',
    () => <SectionHeader title="Header with underline" dividing />
  )
  .add(
    'Header with help block',
    () => <SectionHeader title="Header" description="Page help block" dividing />
  )
;
