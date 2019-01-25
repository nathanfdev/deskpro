import React from 'react';
import { storiesOf, action } from '@storybook/react';
import { SideBar } from 'DeskPRO/Bundle/AgentBundle/Modules/SideBar/Components/SideBar';
import { css, redux } from '../../../decorators';

storiesOf('App: side bar', module)
  .addDecorator(story => css(story()))
  .addDecorator(story => redux({}, story()))
  .add(
    'Side bar',
    () => <div>
      <SideBar
        canUseTicket={() => true}
        canUseChat={() => true}
        canUseFeedback={() => true}
        canUsePeople={() => true}
        canUsePublish={() => true}
        canUseReports={() => false}
        canUseTasks={() => true}
        canUseAdmin={() => true}
        canUseBilling={() => false}
        canUsePortal={() => true}
        changeSection={action('Change section')}
        sectionsBadges={[]}
      />
    </div>
  )
  .add(
    'Side bar selected',
    () => <div>
      <SideBar
        canUseTicket={() => true}
        canUseChat={() => true}
        canUseFeedback={() => true}
        canUsePeople={() => true}
        canUsePublish={() => true}
        canUseReports={() => false}
        canUseTasks={() => true}
        canUseAdmin={() => true}
        canUseBilling={() => false}
        canUsePortal={() => true}
        currentSection={'menu_tickets'}
        sectionsBadges={[]}
      />
    </div>
  )
  .add(
    'Side bar with onboarding',
    () => <div>
      <SideBar
        canUseTicket={() => true}
        canUseChat={() => true}
        canUseFeedback={() => false}
        canUsePeople={() => true}
        canUsePublish={() => true}
        canUseReports={() => false}
        canUseTasks={() => false}
        canUseAdmin={() => false}
        canUseBilling={() => false}
        canUsePortal={() => true}
        currentSection={'menu_tickets'}
        logoActive
        logoCallback={action('Click logo')}
        sectionsBadges={[]}
      />
    </div>
  )
;
