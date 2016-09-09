import React from 'react';
import Immutable from 'immutable';
import { storiesOf, action } from '@kadira/storybook';
import { EmailsAndBlockMenu } from 'DeskPRO/Bundle/AdminBundle/Modules/EmailTemplates/Components/Menus/EmailsAndBlockMenu';
import {
  emailBlocks
} from '../../../../DemoState/AdminBundle/Modules/Application/email_templates';
import { adminCss } from '../../../decorators';

storiesOf('Admin: email templates', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Email and blocks menu',
    () => <div>
      <EmailsAndBlockMenu
        emails={Immutable.fromJS(emailBlocks.list.user.groups)}
        emailBlocks={Immutable.fromJS(emailBlocks.list.layout.groups.top.subGroups.primary.templates)}
        onChangeMenu={action('Select Menu')}
        selectTemplate={action('Select Template')}
      />
    </div>
  )
;
