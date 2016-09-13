import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { SideBar } from 'DeskPRO/Bundle/AgentBundle/Modules/SideBar/Components/SideBar';
import { css } from '../../../decorators';

storiesOf('App: side bar', module)
  .addDecorator(story => css(story()))
  .add(
    'Side bar',
    () => <div>
      <SideBar
        canUseTicket
        canUseChat
        canUseFeedback
        canUsePeople
        canUsePublish
        canUseReports={false}
        canUseTasks
        canUseAdmin
        canUseBilling={false}
        canUsePortal
        changeSection={action('Change section')}

      />
    </div>
  )
  .add(
    'Side bar selected',
    () => <div>
      <SideBar
        canUseTicket
        canUseChat
        canUseFeedback
        canUsePeople
        canUsePublish
        canUseReports={false}
        canUseTasks
        canUseAdmin
        canUseBilling={false}
        canUsePortal
        currentSection={'menu_tickets'}
      />
    </div>
  )
  .add(
    'Side bar with onboarding',
    () => <div>
      <SideBar
        canUseTicket
        canUseChat
        canUseFeedback={false}
        canUsePeople
        canUsePublish
        canUseReports={false}
        canUseTasks={false}
        canUseAdmin={false}
        canUseBilling={false}
        canUsePortal
        currentSection={'menu_tickets'}
        logoActive
        logoCallback={action('Click logo')}
      />
    </div>
  )
;
