(function() {
  define([], function() {
    var Admin_Usersources_Helper_UsersourceTypeDecider;
    Admin_Usersources_Helper_UsersourceTypeDecider = (function() {
      function Admin_Usersources_Helper_UsersourceTypeDecider() {
        return;
      }

      Admin_Usersources_Helper_UsersourceTypeDecider.prototype.decide = function($state) {
        if ($state.includes('crm')) {
          return 'user';
        } else if ($state.includes('agents')) {
          return 'agent';
        } else {
          return null;
        }
      };

      return Admin_Usersources_Helper_UsersourceTypeDecider;

    })();
    return new Admin_Usersources_Helper_UsersourceTypeDecider();
  });

}).call(this);

//# sourceMappingURL=UsersourceTypeDecider.js.map
