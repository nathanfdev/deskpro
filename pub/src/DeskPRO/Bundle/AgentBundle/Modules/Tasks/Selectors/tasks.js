import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

export const currentNavSelector = hashStateSelectorFactory(['nav', 'active']);
export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');