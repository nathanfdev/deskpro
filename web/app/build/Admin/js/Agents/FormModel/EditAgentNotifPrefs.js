(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var EditAgentNotifPrefs;
    return EditAgentNotifPrefs = (function() {
      function EditAgentNotifPrefs(prefsTable) {
        this.prefsTable = prefsTable;
      }

      EditAgentNotifPrefs.prototype.getFilterSubs = function() {
        var col, filterSubs, getFilterSubObj, groupName, opt, row, subObj, typeName, vals, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _m, _ref, _ref1, _ref2, _ref3;
        filterSubs = {};
        getFilterSubObj = function(id) {
          if (filterSubs[id]) {
            return filterSubs[id];
          }
          filterSubs[id] = {
            filter_id: id,
            email: [],
            alert: []
          };
          return filterSubs[id];
        };
        _ref = ['sys_filters', 'custom_filters'];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          groupName = _ref[_i];
          _ref1 = ['email', 'alert'];
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            typeName = _ref1[_j];
            if (this.prefsTable.subs["" + groupName + "_" + typeName] == null) {
              continue;
            }
            _ref2 = this.prefsTable.subs["" + groupName + "_" + typeName].rows;
            for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
              row = _ref2[_k];
              subObj = getFilterSubObj(row.filter.id);
              _ref3 = row.cols;
              for (_l = 0, _len3 = _ref3.length; _l < _len3; _l++) {
                col = _ref3[_l];
                for (_m = 0, _len4 = col.length; _m < _len4; _m++) {
                  opt = col[_m];
                  if (opt.value) {
                    subObj[typeName].push(opt.name);
                  }
                }
              }
            }
          }
        }
        vals = Util.values(filterSubs);
        vals = vals.filter(function(a) {
          if (a !== "" && a !== false && a !== 0) {
            return true;
          }
        });
        return vals;
      };

      EditAgentNotifPrefs.prototype.getOtherSubs = function() {
        var appSubs, getAppSubObj, groupName, opt, row, shortName, subObj, vals, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
        appSubs = {};
        getAppSubObj = function(id) {
          if (appSubs[id]) {
            return appSubs[id];
          }
          appSubs[id] = {
            type: id,
            email: [],
            alert: []
          };
          return appSubs[id];
        };
        _ref = ['chat', 'crm', 'feedback', 'publish', 'task', 'twitter'];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          groupName = _ref[_i];
          if (this.prefsTable.subs[groupName] == null) {
            continue;
          }
          _ref1 = this.prefsTable.subs[groupName].rows;
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            row = _ref1[_j];
            subObj = getAppSubObj(groupName);
            _ref2 = row.cols;
            for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
              opt = _ref2[_k];
              if (opt.value) {
                if (opt.name.match(/_email$/)) {
                  shortName = opt.name.replace(/_email$/, '');
                  subObj.email.push(shortName);
                } else {
                  shortName = opt.name.replace(/_alert$/, '');
                  subObj.alert.push(shortName);
                }
              }
            }
          }
        }
        vals = Util.values(appSubs);
        vals = vals.filter(function(a) {
          if (a !== "" && a !== false && a !== 0) {
            return true;
          }
        });
        return vals;
      };

      return EditAgentNotifPrefs;

    })();
  });

}).call(this);

//# sourceMappingURL=EditAgentNotifPrefs.js.map
