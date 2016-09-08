import React from 'react';
import Immutable from 'immutable';
import { storiesOf, action } from '@kadira/storybook';
import { EmailsAndBlockMenu } from 'DeskPRO/Bundle/AdminBundle/Modules/EmailTemplates/Components/Menus/EmailsAndBlockMenu';
import {
  emailStructure
} from '../../../../DemoState/Semantic/menu';
import { adminCss } from '../../../decorators';

storiesOf('Admin: email templates', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Email and blocks menu',
    () => <div>
      <EmailsAndBlockMenu
        emails={Immutable.fromJS(emailStructure.sections[0].items)}
        emailBlocks={Immutable.fromJS(emailStructure.sections[1].items)}
        onChangeMenu={action('Select Menu')}
      />
    </div>
  )
;
