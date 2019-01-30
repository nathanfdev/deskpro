// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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