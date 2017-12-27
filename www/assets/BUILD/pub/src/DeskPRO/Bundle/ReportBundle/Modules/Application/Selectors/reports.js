import { createSelector } from 'reselect';

const stateSelector = state => state.Application.reports;

export const allReportLabelsSelector = createSelector(
  stateSelector,
  state => state.get('labels')
);
