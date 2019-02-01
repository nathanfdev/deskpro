define([], function() {
  class Admin_Usersources_Helper_UsersourceTypeDecider {
    constructor() {
    }

    decide($state) {
      if ($state.includes('crm')) { return 'user'; } else if ($state.includes('agents')) { return 'agent'; } else { return null; }
    }
  }

  return new Admin_Usersources_Helper_UsersourceTypeDecider();
});