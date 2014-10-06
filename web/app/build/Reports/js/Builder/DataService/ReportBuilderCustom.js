(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Reports/Builder/ReportEditFormMapper'], function(BaseListEdit, ReportEditFormMapper) {
    var ReportBuilderCustom;
    return ReportBuilderCustom = (function(_super) {
      __extends(ReportBuilderCustom, _super);

      function ReportBuilderCustom() {
        return ReportBuilderCustom.__super__.constructor.apply(this, arguments);
      }

      ReportBuilderCustom.$inject = ['Api', '$q'];


      /*
      		 *
       */

      ReportBuilderCustom.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/reports/builder/custom').success((function(_this) {
          return function(data) {
            var models;
            models = data.reports;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
       * Remove a model
       *
       * @param {Integer} id
       * @return {promise}
       */

      ReportBuilderCustom.prototype.deleteReportById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/reports/builder/' + id).success((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
      		 * Get the form mapper
      		 *
      		 * @return {ReportEditFormMapper}
       */

      ReportBuilderCustom.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new ReportEditFormMapper();
        return this.formMapper;
      };


      /*
       * Get all data needed for the edit page
       *
       * @param {Integer} id
       * @return {promise}
       */

      ReportBuilderCustom.prototype.loadEditReportData = function(id, params) {
        var data, deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/reports/builder/' + id, {
            params: params
          }).then((function(_this) {
            return function(result) {
              var data;
              if (result.data.type !== 'custom') {
                throw new Error('Report you are loading should be custom report');
              }
              data = {};
              data.report = result.data.report;
              data.rendered_result = result.data.rendered_result;
              data.query_parts = result.data.query_parts;
              data.form = _this.getFormMapper().getFormFromModel(data);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        } else {
          data = {};
          data.report = {
            title: '',
            description: '',
            is_custom: true
          };
          data.rendered_result = '';
          data.query_parts = {};
          data.form = this.getFormMapper().getFormFromModel(data);
          deferred.resolve(data);
        }
        return deferred.promise;
      };


      /*
       * Saves a form model and merges model with list data
       *
       * @param {Object} model report model
       	 * @param {Object} formModel  The model representing the form
       	 * @param {Object} queryParts object that is used for storing query builder data
       * @return {promise}
       */

      ReportBuilderCustom.prototype.saveFormModel = function(model, formModel, queryParts) {
        var mapper, postData, promise;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/reports/builder/' + model.id, {
            report: postData,
            parts: queryParts
          });
        } else {
          promise = this.Api.sendPutJson('/reports/builder', {
            report: postData,
            parts: queryParts
          }).success(function(data) {
            return model.id = data.id;
          });
        }
        promise.success((function(_this) {
          return function(data) {
            if (data.error) {
              return;
            }
            mapper.applyFormToModel(model, formModel);
            return _this.mergeDataModel(model);
          };
        })(this));
        return promise;
      };

      return ReportBuilderCustom;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=ReportBuilderCustom.js.map
