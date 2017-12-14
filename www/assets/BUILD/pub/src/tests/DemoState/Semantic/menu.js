import React from 'react';
import List from 'DeskPRO/Component/Semantic/List/List';


const customList = {
  elements: [
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
  ]
};
export const customStructure = {
  panels: [
    {
      title:       'Custom templates',
      icon:        'open folder',
      subElements: customList.elements,
      content:     <List {...customList} />
    },
  ]
};
export const primaryList = {
  elements: [
    {
      label: 'New ticket auto-response',
      icon:  'code'
    },
    {
      label: 'New ticket by agent',
      icon:  'code'
    },
    {
      label: 'New ticket confirmation',
      icon:  'code'
    },
    {
      label: 'New agent reply',
      icon:  'code'
    },
    {
      label: 'User reply auto-response',
      icon:  'code'
    },
  ]
};
const validationList = {
  elements: [
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'Ticket new Validate email',
      icon:  'code'
    }
  ]
};
const warningList = {
  elements: [
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
  ]
};
const ratingList = {
  elements: [
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
  ]
};
const ccList = {
  elements: [
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    }
  ]
};
export const listStructure = {
  panels: [
    {
      title:       'Validation',
      icon:        'open folder',
      subElements: validationList.elements,
      content:     <List {...validationList} />
    },
    {
      title:       'Warning, alerts & errors',
      icon:        'open folder',
      subElements: warningList.elements,
      content:     <List {...warningList} />
    },
    {
      title:       'Rating',
      icon:        'open folder',
      subElements: ratingList.elements,
      content:     <List {...ratingList} />
    },
    {
      title:       'CC and new participants',
      icon:        'open folder',
      subElements: ccList.elements,
      content:     <List {...ccList} />
    }
  ]
};
export const emailStructure = {
  searchBox: true,
  sections:  [
    {
      title: 'Emails',
      items: [
        {
          label:      'Ticket emails',
          icon:       'folder open',
          subContent: {
            sections: {
              primary:    primaryList.elements,
              custom:     customList.elements,
              additional: listStructure.panels
            }
          }
        },
        {
          label:      'Account emails',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Chat emails',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Feedback emails',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Publish emails',
          icon:       'folder open',
          subContent: {}
        }
      ]
    },
    {
      title: 'Email blocks',
      items: [
        {
          label: 'Header',
          icon:  ''
        },
        {
          label: 'Footer',
          icon:  ''
        },
        {
          label: 'CSS',
          icon:  'code'
        },
        {
          label:      'Other blocks',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Other blocks 2',
          icon:       'folder open',
          subContent: {}
        }
      ]
    }
  ]
};
export const phrasesStructure = {
  searchBox: true,
  sections:  [
    {
      items: [
        {
          label:      'Emails subjects',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Emails',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'General',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Errors',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'User defaults',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Ticket phrases',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Date & time',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Custom',
          icon:       'folder open',
          subContent: {}
        }
      ]
    }
  ]
};

export const variablesStructure = {
  searchBox: true,
  sections:  [
    {
      items: [
        {
          label:      'The ticket',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Agents',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'The organisation',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Messages',
          icon:       'folder open',
          subContent: {}
        },
        {
          label:      'Person properties',
          icon:       'folder open',
          subContent: {}
        }
      ]
    },
    {
      withDivider: true,
      items:       [
        {
          label:      'Settings',
          icon:       'folder open',
          subContent: {}
        }
      ]
    }
  ]
};

