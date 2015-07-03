import DpApi form "DeskPRO/AgentBundle/Application/Service/DpApi";
import * as ReactUtils from "DeskPRO/Component/React/Utils";

export default UserActions = ReactUtils.createActions({
  @ReactUtils.asyncAction()
  loadUser() {
    return DpApi.sendGet('DP_API/me');
  }
});
