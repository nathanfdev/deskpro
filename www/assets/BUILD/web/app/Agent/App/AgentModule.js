define([
  'Agent/AppPlatform/AgentApp',
  'Agent/App/Service/CurrentUserData'
], (
  AgentApp,
  Service_CurrentUserData
) => {
  AgentApp.service('CurrentUserData', Service_CurrentUserData);

  return AgentApp;
});
