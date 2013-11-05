(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/TicketMacros/MacroEditFormMapper'], function(BaseListEdit, MacroEditFormMapper) {
    var Admin_TicketFilters_DataService_TicketMacros, _ref;
    return Admin_TicketFilters_DataService_TicketMacros = (function(_super) {
      __extends(Admin_TicketFilters_DataService_TicketMacros, _super);

      function Admin_TicketFilters_DataService_TicketMacros() {
        _ref = Admin_TicketFilters_DataService_TicketMacros.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFilters_DataService_TicketMacros.$inject = ['Api', '$q'];

      Admin_TicketFilters_DataService_TicketMacros.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_macros').success(function(data) {
          var models;
          models = data.macros;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Remove a filter
        	#
        	# @param {Integer} id Filter id
        	# @return {promise}
      */


      Admin_TicketFilters_DataService_TicketMacros.prototype.deleteMacroById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/ticket_macros/' + id).then(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
        	# Get all data needed for the edit filter page
        	#
        	# @param {Integer} id Filter id
        	# @return {promise}
      */


      Admin_TicketFilters_DataService_TicketMacros.prototype.loadEditMacroData = function(id) {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendDataGet({
          'macro': (id ? '/ticket_macros/' + id : null),
          'agents': '/agents'
        }).then(function(result) {
          var data, _ref1;
          data = {};
          if (((_ref1 = result.data.macro) != null ? _ref1.macro : void 0) != null) {
            data.macro = result.data.macro.macro;
          } else {
            data.macro = {
              id: null,
              title: ''
            };
          }
          data.agents = result.data.agents.agents;
          data.form = _this.getFormMapper().getFormFromModel(data.macro);
          return deferred.resolve(data);
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Get the form mapper
        	#
        	# @return {MacroEditFormMapper}
      */


      Admin_TicketFilters_DataService_TicketMacros.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new MacroEditFormMapper();
        return this.formMapper;
      };

      /*
        	# Saves a form model and applies the form model to the macro model
        	# once finished.
        	#
        	# @param {Object} macroModel The macro model
        	# @param {Object} formModel  The model representing the form
        	# @return {promise}
      */


      Admin_TicketFilters_DataService_TicketMacros.prototype.saveFormModel = function(macroModel, formModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (macroModel.id) {
          promise = this.Api.sendPostJson('/ticket_macros/' + macroModel.id, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_macros', postData).success(function(data) {
            return macroModel.id = data.macro_id;
          });
        }
        promise.success(function() {
          mapper.applyFormToModel(macroModel, formModel);
          return _this.mergeDataModel(macroModel);
        });
        return promise;
      };

      return Admin_TicketFilters_DataService_TicketMacros;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=TicketMacros.js.map
*/