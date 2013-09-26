(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_MainPage, _ref;
    Admin_Main_Ctrl_MainPage = (function(_super) {
      __extends(Admin_Main_Ctrl_MainPage, _super);

      function Admin_Main_Ctrl_MainPage() {
        _ref = Admin_Main_Ctrl_MainPage.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Main_Ctrl_MainPage.CTRL_ID = 'Admin_Main_Ctrl_MainPage';

      Admin_Main_Ctrl_MainPage.DEPS = ['$rootScope', 'AppState'];

      Admin_Main_Ctrl_MainPage.prototype.init = function() {
        var _this = this;
        this.$scope.loading = {
          dp_section_page: false,
          dp_section_list: false
        };
        this.AppState.addListener('statechange_dp_section_page', function(val) {
          return _this.$scope.loading.dp_section_page = val;
        });
        this.AppState.addListener('statechange_dp_section_list', function(val) {
          return _this.$scope.loading.dp_section_list = val;
        });
        return this.$rootScope.$on('$stateChangeStart', function(ev, toState, toParams, fromState, fromParams) {
          var from_group, from_list, from_page, id_segs, to_group, to_list, to_page;
          if (ev.defaultPrevented) {
            return;
          }
          id_segs = toState.name.split('.');
          if (id_segs.length < 2) {
            return;
          }
          to_group = id_segs.shift();
          to_list = id_segs.shift();
          to_page = id_segs.shift();
          if (toParams.id != null) {
            to_page += '.' + toParams.id;
          }
          from_group = from_list = from_page = null;
          if (fromState) {
            id_segs = fromState.name.split('.');
            if (id_segs.length >= 2) {
              from_group = id_segs.shift();
              from_list = id_segs.shift();
              from_page = id_segs.shift();
              if (fromParams.id != null) {
                from_page += '.' + fromParams.id;
              }
            }
          }
          if (to_page && to_page !== from_page) {
            _this.AppState.setLoadingState('dp_section_page', true);
          }
          if (to_list && to_list !== from_list) {
            return _this.AppState.setLoadingState('dp_section_list', true);
          }
        });
      };

      return Admin_Main_Ctrl_MainPage;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_MainPage.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=MainPage.js.map
*/