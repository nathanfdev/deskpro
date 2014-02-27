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

      ReportBuilderCustom.prototype.init = function() {
        return this.setSubLists(['Tickets', 'Chats', 'Ideas', 'People & Organizations', 'Knowledgebase', 'News', 'Downloads', 'Feedback', 'Tasks', 'Twitter']);
      };


      /*
      		 *
       */

      ReportBuilderCustom.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/reports/builder/builtIn').success((function(_this) {
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
        var deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/reports/builder/' + id, {
            params: params
          }).then((function(_this) {
            return function(result) {
              var data;
              if (result.data.type !== 'builtIn') {
                throw new Error('Report you are loading should be built-in report');
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
          this.Api.sendGet('/reports/builder').then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.report = {
                user: {}
              };
              data.form = _this.getFormMapper().getFormFromModel(data);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        }
        return deferred.promise;
      };

      return ReportBuilderCustom;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=ReportBuilderBuiltIn.js.map
