import React from 'react';
import ImporterStatus from 'DeskPRO/Bundle/AdminBundle/Modules/Apps/Components/Importer/Status/ImporterStatus';
import { storiesOf } from '@kadira/storybook';
import { adminCss } from '../../../../decorators';

storiesOf('Admin: Importer', module)
  .addDecorator(story => adminCss(story()))
  .add(
    'Importer status init',
    () =>
      <ImporterStatus
        title="My Importer"
        status="waiting"
        steps={['person', 'ticket', 'organization']}
      />
  )
  .add(
    'Importer status processing',
    () =>
      <ImporterStatus
        title="My Importer"
        status="processing"
        steps={['person', 'ticket', 'organization']}
        importedSteps={['person', 'ticket']}
        importedCounts={{ person: 200, ticket: 600 }}
        log="
          Imported person#1<br />
          Imported person#2<br />
          Imported person#3<br />
<br />
          Imported ticket#1<br />
          Imported ticket#2<br />
          Imported ticket#3<br />
        "
      />
  )
  .add(
    'Importer status finished',
    () =>
      <ImporterStatus
        title="My Importer"
        status="complete"
        steps={['person', 'ticket', 'organization']}
        importedSteps={['person', 'ticket', 'organization']}
        importedCounts={{ person: 200, ticket: 600, organization: 100 }}
        appliedSteps={['person', 'ticket', 'organization']}
        appliedCounts={{ person: 200, ticket: 600, organization: 100 }}
        log="
          Imported person#1<br />
          Imported person#2<br />
          Imported person#3<br />
          Imported person#4<br />
          Imported person#5<br />
          Imported person#6<br />
          Imported person#7<br />
          Imported person#8<br />
          Imported person#9<br />
          Imported person#10<br />
          Imported person#11<br />
          Imported person#12<br />
          Imported person#13<br />
          Imported person#14<br />
          Imported person#15<br />
<br />
          Imported ticket#1<br />
          Imported ticket#2<br />
          Imported ticket#3<br />
          Imported ticket#4<br />
          Imported ticket#5<br />
          Imported ticket#6<br />
          Imported ticket#7<br />
          Imported ticket#8<br />
          Imported ticket#9<br />
          Imported ticket#10<br />
          Imported ticket#11<br />
          Imported ticket#12<br />
          Imported ticket#13<br />
          Imported ticket#14<br />
          Imported ticket#15<br />
<br />
          Imported organization#1<br />
          Imported organization#2<br />
          Imported organization#3<br />
          Imported organization#4<br />
          Imported organization#5<br />
          Imported organization#6<br />
          Imported organization#7<br />
          Imported organization#8<br />
          Imported organization#9<br />
          Imported organization#10<br />
          Imported organization#11<br />
          Imported organization#12<br />
          Imported organization#13<br />
          Imported organization#14<br />
          Imported organization#15<br />
        "
      />
  )
;
