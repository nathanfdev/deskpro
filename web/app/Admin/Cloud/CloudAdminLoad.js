define([
  'Admin/Cloud/Main/Ctrl/Home',
  'Admin/Cloud/License/Ctrl/License',
  'Admin/Cloud/Settings/Ctrl/CustomDomain',
  'Admin/Cloud/TicketAccounts/Ctrl/Edit',
], function() {
  if (window.DP_IS_CLOUD) {
    console.info("Cloud Mode Enabled");
  }
})