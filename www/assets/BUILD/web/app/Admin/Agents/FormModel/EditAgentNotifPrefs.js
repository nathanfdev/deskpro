// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Util'], function(Util) {
  let EditAgentNotifPrefs;
  return (EditAgentNotifPrefs = class EditAgentNotifPrefs {



    constructor(prefsTable) {
      this.prefsTable = prefsTable;
    }



    getFilterSubs() {
      const filterSubs = {};

      const getFilterSubObj = function(id) {
        if (filterSubs[id]) { return filterSubs[id]; }
        return filterSubs[id] = {
          filter_id: id,
          email: [],
          alert: []
        };
      };

      for (let groupName of ['sys_filters', 'custom_filters']) {
        for (let typeName of ['email', 'alert']) {
          if ((this.prefsTable.subs[`${groupName}_${typeName}`] == null)) { continue; }

          for (let row of Array.from(this.prefsTable.subs[`${groupName}_${typeName}`].rows)) {
            const subObj = getFilterSubObj(row.filter.id);

            for (let col of Array.from(row.cols)) {
              for (let opt of Array.from(col)) {
                if (opt.value) {
                  subObj[typeName].push(opt.name);
                }
              }
            }
          }
        }
      }

      const vals = Util.values(filterSubs);
      return vals.filter(function(a) { if ((a !== "") && (a !== false) && (a !== 0)) { return true; } });
    }



    getOtherSubs() {
      const appSubs = {};

      const getAppSubObj = function(id) {
        if (appSubs[id]) { return appSubs[id]; }
        return appSubs[id] = {
          type: id,
          email: [],
          alert: []
        };
      };

      for (let groupName of ['chat', 'crm', 'feedback', 'publish', 'task', 'twitter', 'account']) {
        if ((this.prefsTable.subs[groupName] == null)) { continue; }

        for (let row of Array.from(this.prefsTable.subs[groupName].rows)) {
          const subObj = getAppSubObj(groupName);

          for (let opt of Array.from(row.cols)) {
            if (opt.value) {
              var shortName;
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

      const vals = Util.values(appSubs);
      return vals.filter(function(a) { if ((a !== "") && (a !== false) && (a !== 0)) { return true; } });
    }
  });
});
