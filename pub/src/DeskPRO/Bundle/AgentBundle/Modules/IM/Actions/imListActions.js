import { createAction } from 'Ampliflux/actions';
import * as Agents from 'DeskPRO/Bundle/AgentBundle/Services/Api/Agents';

export const loadAgents = createAction(
    'IM_LIST_LOAD_AGENTS',
    (trigger) => {
        return Agents.loadAgents().then(promise => trigger(promise.getData().data))
    }
);

