import React from 'react';
import Immutable from 'immutable';
import { storiesOf, action } from '@kadira/storybook';
import { EmailsAndBlockMenu } from 'DeskPRO/Bundle/AdminBundle/Modules/EmailTemplates/Components/Menus/EmailsAndBlockMenu';
import { VariablesMenu } from 'DeskPRO/Bundle/AdminBundle/Modules/EmailTemplates/Components/Menus/VariablesMenu';
import { MediaMenu } from 'DeskPRO/Bundle/AdminBundle/Modules/EmailTemplates/Components/Menus/MediaMenu';
import {
  emailBlocks,
  variables,
  mediaInline,
  mediaAttachments
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
  .add(
    'Variables menu',
    () => <div>
      <VariablesMenu
        viewModel={Immutable.fromJS(variables)}
      />
    </div>
  )
  .add(
    'Media menu',
    () => <div>
      <MediaMenu
        inlineFiles={Immutable.fromJS(mediaInline)}
        attachmentFiles={Immutable.fromJS(mediaAttachments)}
        insertAttachment={action('Insert Attachment')}
        insertInlineImage={action('Insert Inline Image')}
        downloadFile={action('Download File')}
        reloadFiles={action('Reload File')}
        deleteFile={action('Delete File')}
        onSend={action('On Send')}
        onFail={action('On Fail')}
      />
    </div>
  )
;
