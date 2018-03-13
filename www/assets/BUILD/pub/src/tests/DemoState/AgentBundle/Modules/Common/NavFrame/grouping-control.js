import { action } from '@storybook/react';

const selectDemoOptions = [
  { label: 'First ID #1 option', value: 1 },
  { label: 'Second ID #2 option', value: 2 },
  { label: 'Third ID #3 option', value: 3 }
];

/**
 * ListGroupingForm demo props
 */
export const formDemoProps = {
  options:  selectDemoOptions,
  title:    'Demo Grouping Control',
  apply:    action('apply'),
  selected: 2
};

/**
 * ListGroupingModal demo props
 */
export const modalDemoProps = {
  ...formDemoProps,

  attachTo: null,
  close:    action('close'),
  visible:  true
};
