(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Main_Ctrl_Base) {
    var Admin_ChannelFacebook_Ctrl_List;
    Admin_ChannelFacebook_Ctrl_List = (function(_super) {
      __extends(Admin_ChannelFacebook_Ctrl_List, _super);

      function Admin_ChannelFacebook_Ctrl_List() {
        return Admin_ChannelFacebook_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_ChannelFacebook_Ctrl_List.CTRL_ID = 'Admin_ChannelFacebook_Ctrl_List';

      Admin_ChannelFacebook_Ctrl_List.CTRL_AS = 'ChannelFacebookList';

      Admin_ChannelFacebook_Ctrl_List.CTRL_TYPE = 'list';

      Admin_ChannelFacebook_Ctrl_List.DEPS = ['FacebookPagesData'];

      Admin_ChannelFacebook_Ctrl_List.prototype.init = function() {
        return this.pages = [];
      };

      Admin_ChannelFacebook_Ctrl_List.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.FacebookPagesData.loadList().then((function(_this) {
          return function(recs) {
            var acc, accounts, _i, _len;
            _this.pages = [];
            accounts = recs.values();
            for (_i = 0, _len = accounts.length; _i < _len; _i++) {
              acc = accounts[_i];
              _this.pages.push(acc);
            }
            _this.pages.push({
              id: 5,
              identifier: 'Camp Happy',
              is_enabled: true,
              img: 'computer'
            });
            _this.pages.push({
              id: 9,
              identifier: 'Big Corp.',
              is_enabled: false,
              img: 'taxi'
            });
            if (_this.$state.current.name === 'tickets.channel_facebook') {
              if (_this.pages[0]) {
                _this.$state.go('tickets.channel_facebook.edit', {
                  id: _this.pages[0].id
                });
              } else {
                _this.$state.go('tickets.channel_facebook.create');
              }
            }
            return _this.addManagedListener(_this.FacebookPagesData.recs, 'changed', function() {
              var _j, _len1, _ref;
              _this.pages = [];
              accounts = _this.FacebookPagesData.recs.values();
              for (_j = 0, _len1 = accounts.length; _j < _len1; _j++) {
                acc = accounts[_j];
                acc.phone_number_region = (_ref = acc.phone_number_region) != null ? _ref.toLowerCase() : void 0;
                _this.pages.push(acc);
              }
              return _this.ngApply();
            });
          };
        })(this));
        return this.$q.all([list_promise]);
      };

      Admin_ChannelFacebook_Ctrl_List.prototype.startDelete = function(for_acc_id) {
        var for_acc, inst, v, _i, _len, _ref;
        for_acc = null;
        _ref = this.pages;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          v = _ref[_i];
          if (v.id === for_acc_id) {
            for_acc = v;
          }
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('ChannelFacebook/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function() {
            return _this.deleteAccount(for_acc);
          };
        })(this));
      };

      Admin_ChannelFacebook_Ctrl_List.prototype.deleteAccount = function(acc) {
        return this.Api.sendDelete('/channel/facebook/page/' + acc.id).success((function(_this) {
          return function() {
            _this.FacebookPagesData.remove(acc.id);
            _this.ngApply();
            if (_this.$state.current.name === 'tickets.channel_sms.edit' && parseInt(_this.$state.params.id) === acc.id) {
              return _this.$state.go('tickets.channel_sms');
            }
          };
        })(this));
      };

      return Admin_ChannelFacebook_Ctrl_List;

    })(Admin_Main_Ctrl_Base);
    return Admin_ChannelFacebook_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
