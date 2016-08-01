import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import List from 'Components/List/List';

storiesOf('App: list', module)
  .add(
    'Simple List',
    () => <List {...structure} />
  )
  .add(
    'List with description',
    () => <List {...structureDescription} />
  )
  .add(
    'List with hierarchy',
    () => <List {...hierarchy} />
  )
;
const structure = {
  elements: [
    {
      label: 'Boat',
      icon: 'ship'
    },
    {
      label: 'Motorcycle',
      icon: 'motorcycle'
    },
    {
      label: 'Taxi',
      icon: 'taxi'
    },
    {
      label: 'Train',
      icon: 'train'
    },
    {
      label: 'Space Shuttle',
      icon: 'space shuttle'
    }
  ]
};
const structureDescription = {
  elements: [
    {
      label: 'Krowlewskie Jadlo',
      description: 'An excellent polish restaurant, quick delivery and hearty, filling meals.',
      icon: 'map'
    },
    {
      label: 'Xian Famous Foods',
      description: 'A taste of Shaanxi\'s delicious culinary traditions, with delights like spicy cold noodles and lamb burgers.',
      icon: 'map'
    },
    {
      label: 'Sapporo Haru',
      description: 'Greenpoint\'s best choice for quick and delicious sushi.',
      icon: 'map'
    },
    {
      label: 'Enid\'s',
      description: 'At night a bar, during the day a delicious brunch spot.',
      icon: 'map'
    }
  ]
};
const hierarchy = {
  elements: [
    {
      label: 'src',
      icon: 'folder',
      description: 'Source files for project',
      elements: [
        {
          label: 'site',
          icon: 'folder',
          description: 'Your site\'s theme'
        },
        {
          label: 'themes',
          icon: 'folder',
          description: 'Packaged theme files',
          elements: [
            {
              label: 'default',
              icon: 'folder',
              description: 'Default packaged theme'
            },
            {
              label: 'my_theme',
              icon: 'folder',
              description: 'Packaged themes are also available in this folder'
            }
          ]
        },
        {
          label: 'theme.config',
          icon: 'folder',
          description: 'Config file for setting packaged themes'
        },
      ]
    },
    {
      label: 'dist',
      icon: 'folder',
      description: 'Compiled CSS and JS files',
      elements: [
        {
          label: 'components',
          icon: 'folder',
          description: 'Individual component CSS and JS'
        }
      ]
    },
    {
      label: 'semantic.json',
      icon: 'folder',
      description: 'Contains build settings for gulp'
    },
  ]
};