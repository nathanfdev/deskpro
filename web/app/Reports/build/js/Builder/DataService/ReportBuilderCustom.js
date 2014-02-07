(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Reports/Builder/ReportEditFormMapper'], function(BaseListEdit, ReportEditFormMapper) {
    var ReportBuilderCustom, _ref;
    return ReportBuilderCustom = (function(_super) {
      __extends(ReportBuilderCustom, _super);

      function ReportBuilderCustom() {
        _ref = ReportBuilderCustom.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      ReportBuilderCustom.$inject = ['Api', '$q'];

      /*
      		#
      */


      ReportBuilderCustom.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/reports/builder/custom').success(function(data) {
          var models;
          models = data.reports;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
      # Remove a model
      #
      # @param {Integer} id
      # @return {promise}
      */


      ReportBuilderCustom.prototype.deleteReportById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/reports/builder/' + id).success(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
      		# Get the form mapper
      		#
      		# @return {ReportEditFormMapper}
      */


      ReportBuilderCustom.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new ReportEditFormMapper();
        return this.formMapper;
      };

      /*
      # Get all data needed for the edit page
      #
      # @param {Integer} id
      # @return {promise}
      */


      ReportBuilderCustom.prototype.loadEditReportData = function(id) {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/reports/builder/' + id).then(function(result) {
            var data;
            if (result.data.type !== 'custom') {
              throw new Error('Report you are loading should be custom report');
            }
            data = {};
            data.report = result.data.report;
            data.rendered_result = result.data.rendered_result;
            data.form = _this.getFormMapper().getFormFromModel(data);
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        } else {
          this.Api.sendGet('/reports/builder').then(function(result) {
            var data;
            data = {};
            data.report = {
              user: {}
            };
            data.form = _this.getFormMapper().getFormFromModel(data);
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        }
        return deferred.promise;
      };

      /*
      # Saves a form model and merges model with list data
      #
      # @param {Object} model api_key model
       	# @param {Object} formModel  The model representing the form
      # @return {promise}
      */


      ReportBuilderCustom.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/reports/builder/' + model.id, {
            report: postData
          });
        } else {
          promise = this.Api.sendPutJson('/reports/builder', {
            report: postData
          }).success(function(data) {
            return model.id = data.id;
          });
        }
        promise.success(function() {
          mapper.applyFormToModel(model, formModel);
          return _this.mergeDataModel(model);
        });
        return promise;
      };

      return ReportBuilderCustom;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=ReportBuilderCustom.js.map
*/