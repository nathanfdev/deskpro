import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

export const currentNavSelector = hashStateSelectorFactory(['nav', 'active']);
export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
export const currentOrderSelector = hashStateSelectorFactory(['list', 'order'], 'desc');
export const currentSortSelector = hashStateSelectorFactory(['list', 'sort'], 'desc');
