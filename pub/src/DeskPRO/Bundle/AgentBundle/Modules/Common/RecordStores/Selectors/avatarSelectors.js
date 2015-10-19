export function personAvatarsStateSelector(state) {
  return state.RecordStores.Common.Avatars.person;
}
export function organizationAvatarsStateSelector(state) {
  return state.RecordStores.Common.Avatars.organization;
}
export function agentTeamAvatarsStateSelector(state) {
  return state.RecordStores.Common.Avatars.agentTeam;
}
export function departmentAvatarsStateSelector(state) {
  return state.RecordStores.Common.Avatars.department;
}