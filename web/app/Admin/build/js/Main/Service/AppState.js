(function() {
  define(['Admin/Main/Util/EventsMixin'], function(EventsMixin) {
    var AppState;
    return AppState = (function() {
      function AppState($rootScope, $state) {
        var _this = this;
        this.$rootScope = $rootScope;
        this.$state = $state;
        EventsMixin(this);
        this.vars = {};
        this.activeState = null;
        this.activeStateApp = null;
        this.activeStateNav = null;
        this.activeStateList = null;
        this.sectionState = null;
        this.loadingState = {};
        this.$rootScope.$on('$viewContentLoaded', function() {
          var current_state_id, _ref, _ref1;
          if (_this.$state.current) {
            current_state_id = $state.current.name;
            if ($state.params.id) {
              current_state_id += '.' + $state.params.id;
            }
            _this.setActiveState(current_state_id);
            if (_this.$state.current.with_list_view) {
              $(document.body).addClass('with-section-list');
            } else {
              $(document.body).removeClass('with-section-list');
            }
            if (_this.$state.current.with_nav_view) {
              $(document.body).addClass('with-section-nav');
            } else {
              $(document.body).removeClass('with-section-nav');
            }
            if ((_ref = window.parent) != null ? _ref.DP_FRAME_OVERLAY_admin : void 0) {
              return (_ref1 = window.parent) != null ? _ref1.DP_FRAME_OVERLAY_admin.setHash((window.location.hash + '').substring(1)) : void 0;
            }
          }
        });
        this.$rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
          var full, _ref, _ref1;
          full = toState.name;
          if (toParams.id != null) {
            full += '.' + toParams.id;
          }
          _this.setActiveState(full);
          if (_this.$state.current.with_list_view) {
            $(document.body).addClass('with-section-list');
          } else {
            $(document.body).removeClass('with-section-list');
          }
          if ((_ref = window.parent) != null ? _ref.DP_FRAME_OVERLAY_admin : void 0) {
            return (_ref1 = window.parent) != null ? _ref1.DP_FRAME_OVERLAY_admin.setHash((window.location.hash + '').substring(1)) : void 0;
          }
        });
      }

      AppState.prototype.setLoadingState = function(id, val) {
        if (!window.DP_SECTION_LOADING_STATE) {
          window.DP_SECTION_LOADING_STATE = {};
        }
        window.DP_SECTION_LOADING_STATE[id] = val;
        if (this.loadingState[id] !== val) {
          this.loadingState[id] = val;
          this.notifyListeners('statechange', [id, val]);
          return this.notifyListeners('statechange_' + id, [val]);
        }
      };

      AppState.prototype.isStateActive = function(stateId) {
        var stateIdRegex;
        if (!stateId || !this.activeState) {
          return false;
        }
        stateIdRegex = '^';
        stateIdRegex += stateId.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
        stateIdRegex += '\\b';
        if (this.activeState.match(new RegExp(stateIdRegex))) {
          return true;
        } else {
          return false;
        }
      };

      AppState.prototype.setActiveState = function(activeState) {
        var bits;
        if (this.activeState === activeState) {
          return;
        }
        this.activeState = activeState;
        bits = this.activeState.split('.');
        this.activeStateApp = bits.shift();
        this.activeStateNav = bits.shift();
        this.activeStateList = bits.shift();
        this.activeStatePage = null;
        if (bits.length) {
          this.activeStatePage = bits.join('.');
        }
        return this.$rootScope.$broadcast('dp_activeStateChange', this.activeState, this.activeStateApp, this.activeStateNav, this.activeStateList);
      };

      return AppState;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=AppState.js.map
*/