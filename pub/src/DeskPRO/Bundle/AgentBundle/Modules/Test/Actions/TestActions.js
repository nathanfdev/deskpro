import { createAction } from "Ampliflux";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const setCount = createAction("SET_COUNT");
export const loadUser = createAction("TEST_LOAD_USER", () => {
  return DpApi.sendGet('DP_API/me');
});
