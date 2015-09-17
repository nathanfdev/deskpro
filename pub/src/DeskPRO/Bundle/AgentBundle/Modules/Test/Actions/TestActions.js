import { createAction } from "Ampliflux";
import { loadUsers } from "DeskPRO/Bundle/AgentBundle/Modules/Common/Actions/UserActions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const setCount = createAction("SET_COUNT");
export const loadUser = createAction("TEST_LOAD_USER", () => {
  return DpApi.sendGet('DP_API/me');
});

export const loadUserBatch = createAction("TEST_LOAD_USERS", (ids) => loadUsers("testApp", ids));
