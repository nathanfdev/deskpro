(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Usersources;
    return Usersources = (function(_super) {
      __extends(Usersources, _super);

      function Usersources() {
        return Usersources.__super__.constructor.apply(this, arguments);
      }

      Usersources.$inject = ['Api', '$q'];

      Usersources.prototype.init = function() {};


      /*
      		 * Update display orders
      		 *
      		 * @param {Array} Array of IDs in order
      		 * @return {promise}
       */

      Usersources.prototype.saveDisplayOrder = function(display_orders) {
        return this.Api.sendPostJson('/usersources/display-order', {
          display_orders: display_orders
        });
      };

      return Usersources;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=Usersources.js.map
