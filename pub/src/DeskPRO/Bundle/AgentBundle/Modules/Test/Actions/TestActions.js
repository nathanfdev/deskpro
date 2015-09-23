import { createAction } from "Ampliflux/actions";
import { loadPeople } from "DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const setCount = createAction("SET_COUNT");
export const loadUser = createAction("TEST_LOAD_USER", () => {
  return DpApi.sendGet('DP_API/me');
});

export const loadUserBatch = createAction("TEST_LOAD_USERS", (ids) => loadPeople("testApp", ids));
