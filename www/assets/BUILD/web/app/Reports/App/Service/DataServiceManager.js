// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Strings',
  'Reports/Builder/DataService/ReportBuilderCustom',
  'Reports/Builder/DataService/ReportBuilderBuiltIn',
  'Reports/Builder/DataService/ReportWidgetBuiltIn',
  'Reports/Builder/DataService/ReportWidgetCustom',
], function(
  Strings,
  DataService_ReportBuilderCustom,
  DataService_ReportBuilderBuiltIn,
  DataService_ReportWidgetBuiltIn,
  DataService_ReportWidgetCustom,
) {
  const serviceMap = {
    "DataService_ReportBuilderCustom": DataService_ReportBuilderCustom,
    "DataService_ReportBuilderBuiltIn": DataService_ReportBuilderBuiltIn,
    "DataService_ReportWidgetBuiltIn": DataService_ReportWidgetBuiltIn,
    "DataService_ReportWidgetCustom": DataService_ReportWidgetCustom
  };

  /*
   * A simple wrapper around the data services
   */
  class Admin_Main_Service_DataServiceManager {
    constructor($injector) {
      this.$injector = $injector;
      this.ds_cache = {};
      this.registered = {};
    }

    get(serviceId) {
      let obj;
      if (this.ds_cache[serviceId]) {
        obj = this.ds_cache[serviceId];
      } else {
        obj = null;

        // If this class has a custom initXXX method, call that
        // instead uf the default
        const initName = `init${Strings.ucFirst(Strings.toCamelCase(serviceId))}`;
        if (this[initName] != null) {
          obj = this[initName]();
        }

        if (!obj) {
          const name = `DataService_${serviceId}`;
          const constructor = serviceMap[name];

          if (!constructor) {
            throw new Error(`Invalid data service name: ${name}`);
          }

          obj = this.$injector.instantiate(constructor);
        }

        this.ds_cache[serviceId] = obj;
      }

      return obj;
    }
  }
  return Admin_Main_Service_DataServiceManager;
});