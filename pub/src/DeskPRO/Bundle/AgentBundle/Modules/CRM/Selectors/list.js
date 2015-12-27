import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

const stateSelector = state => state.CRM.list;

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
