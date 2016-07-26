import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { MenuWrapper } from 'Component/Menu/MenuWrapper';

storiesOf('App: menu', module)
  .add(
    'Email Menu',
    () => <MenuWrapper structure={emailStructure} />
  )
  .add(
    'Phrases Menu',
    () => <MenuWrapper structure={phrasesStructure} />
  )
  .add(
    'Variables Menu',
    () => <MenuWrapper structure={variablesStructure} />
  )
;
const emailStructure = {
  searchBox: true,
  sections: [
    {
      title: 'Emails',
      items: [
        {
          label: 'Ticket emails',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Account emails',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Chat emails',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Feedback emails',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Publish emails',
          icon: 'folder open',
          subContent: {}
        }
      ]
    },
    {
      title: 'Email blocks',
      items: [
        {
          label: 'Header',
          icon: ''
        },
        {
          label: 'Footer',
          icon: ''
        },
        {
          label: 'CSS',
          icon: 'code'
        },
        {
          label: 'Other blocks',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Other blocks 2',
          icon: 'folder open',
          subContent: {}
        }
      ]
    }
  ]
};
const phrasesStructure = {
  searchBox: true,
  sections: [
    {
      items: [
        {
          label: 'Emails subjects',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Emails',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'General',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Errors',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'User defaults',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Ticket phrases',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Date & time',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Custom',
          icon: 'folder open',
          subContent: {}
        }
      ]
    }
  ]
};

const variablesStructure = {
  searchBox: true,
  sections: [
    {
      items: [
        {
          label: 'The ticket',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Agents',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'The organisation',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Messages',
          icon: 'folder open',
          subContent: {}
        },
        {
          label: 'Person properties',
          icon: 'folder open',
          subContent: {}
        }
      ]
    },
    {
      withDivider: true,
      items: [
        {
          label: 'Settings',
          icon: 'folder open',
          subContent: {}
        }
      ]
    }
  ]
};
