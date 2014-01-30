(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(ReportsBaseCtrl, Util) {
    var Reports_Builder_Ctrl_List, _ref;
    Reports_Builder_Ctrl_List = (function(_super) {
      __extends(Reports_Builder_Ctrl_List, _super);

      function Reports_Builder_Ctrl_List() {
        _ref = Reports_Builder_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Reports_Builder_Ctrl_List.CTRL_ID = 'Reports_Builder_Ctrl_List';

      Reports_Builder_Ctrl_List.CTRL_AS = 'ListCtrl';

      Reports_Builder_Ctrl_List.prototype.init = function() {
        return this.builderData = this.DataService.get('ReportBuilder');
      };

      /*
      		# Loads the list
      */


      Reports_Builder_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.builderData.loadList().then(function(list) {
          return _this.list = list;
        });
        return promise;
      };

      return Reports_Builder_Ctrl_List;

    })(ReportsBaseCtrl);
    return Reports_Builder_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/