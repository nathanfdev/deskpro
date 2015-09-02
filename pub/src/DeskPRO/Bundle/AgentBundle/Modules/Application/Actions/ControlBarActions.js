import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const switchViewMode = createAction(
    ActionTypes.SWITCH_VIEW_MODE,
    (trigger) => {
        trigger();
    }
);

