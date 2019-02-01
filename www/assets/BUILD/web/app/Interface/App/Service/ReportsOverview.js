// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Util'], function(Util) {
  class ReportsOverview {
    constructor(Api, $q) {
      this.overviewUrl = '/reports/overview/data/';
      this.statsUrl = '/reports/overview/get-stats/';
      this.Api = Api;
      this.$q = $q;
      //just a stub, have to be refactored
      this.$scope = {};
    }

    getData(dataKey) {
      dataKey = dataKey.replace(/\-/g, '_');

      const promise = this.Api.sendGet(`${this.overviewUrl}${dataKey}`);
      return promise.then(data => {
        this.$scope[dataKey] = data.data;
        if ((dataKey === 'tickets_user_waiting_time') || (dataKey === 'tickets_response_time')) {
          this.setDataForTableWithBarGraphs(dataKey);
        } else if (dataKey === 'tickets_opened_hour') {
          this.setDataForTicketsOpenedHours();
        } else {
          this.setDataForBarGraphs(dataKey);
        }
        return this.$scope[dataKey];
    });
    }





    /*
     * This method is used in select boxes for defining grouping field and / or other search parameters
     * @param {String} dataKey - using this key data is looked in @$scope
     */
    getStats(dataKey) {
//      @toggleLoadingState(dataKey)

      dataKey = dataKey.replace(/\-/g, '_');

      const promise = this.Api.sendGet(`/reports/overview/get-stats/${dataKey}`, {
        grouping_field: this.$scope[dataKey].grouping_field,
        date_choice: this.$scope[dataKey].date_choice,
        sla_id: this.$scope[dataKey].sla_id
      });

      return promise.success(data => {
//        @toggleLoadingState(dataKey)
        this.$scope[dataKey] = data;

        if ((dataKey === 'tickets_user_waiting_time') || (dataKey === 'tickets_response_time')) {
          this.setDataForTableWithBarGraphs(dataKey);
        } else if (dataKey === 'tickets_opened_hour') {
          this.setDataForTicketsOpenedHours();
        } else {
          this.setDataForBarGraphs(dataKey);
        }
          
        return this.$scope[dataKey];
    });
    }

    /*
     * We need to display bar graphs - so let's pre-calculate some variables
     * @param {String} dataKey - using this key data is looked in @$scope
     */
    setDataForBarGraphs(dataKey) {
      dataKey = dataKey.replace(/\-/g, '_');

      if (Util.isEmpty(this.$scope[dataKey].values)) { this.$scope[dataKey].empty = true; }
      this.$scope[dataKey].stats = [];
      const denominator = this.$scope[dataKey].max || 1;

      return (() => {
        const result = [];
        for (let key in this.$scope[dataKey].titles) {

          if (this.$scope[dataKey].values[key]) {
            let percentage = (this.$scope[dataKey].values[key] / denominator) * 100;
            if (percentage < 1) { percentage = 1; }

            result.push(this.$scope[dataKey].stats.push({
              title: this.$scope[dataKey].titles[key],
              value: this.$scope[dataKey].values[key] || 0,
              left_percentage: percentage,
              right_percentage: 100 - percentage
            }));
          }
        }
        return result;
      })();
    }


    /*
     * We need to display bar graphs - so let's pre-calculate some variables
     * This method is special case of @setDataForBarGraphs()
     */
    setDataForTicketsOpenedHours() {
      if (Util.isEmpty(this.$scope.tickets_opened_hour.values)) { this.$scope.tickets_opened_hour.empty = true; }
      this.$scope.tickets_opened_hour.stats = [];
      this.$scope.tickets_opened_hour.column_width = 100 / Object.keys(this.$scope.tickets_opened_hour.titles).length;
      const denominator = this.$scope.tickets_opened_hour.max || 1;

      return (() => {
        const result = [];
        for (let key in this.$scope.tickets_opened_hour.titles) {

          if (this.$scope.tickets_opened_hour.values[key]) {

            let percentage = (this.$scope.tickets_opened_hour.values[key] / denominator) * 100;
            if (percentage < 1) { percentage = 1; }

            result.push(this.$scope.tickets_opened_hour.stats.push({
              title: this.$scope.tickets_opened_hour.titles[key],
              value: this.$scope.tickets_opened_hour.values[key] || 0,
              percentage
            }));
          } else {
            result.push(this.$scope.tickets_opened_hour.stats.push({
              title: this.$scope.tickets_opened_hour.titles[key]
            }));
          }
        }
        return result;
      })();
    }


    /*
     * We need to display bar graphs - so let's pre-calculate some variables
     * What is special here - we display every piece of data
     * Just for cases with no data we display only labels without graphical bars
     * Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
     * This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
     * @param {String} dataKey - using this key data is looked in @$scope
     */
    setDataForTableWithBarGraphs(dataKey) {
      dataKey = dataKey.replace(/\-/g, '_');
      
      if (Util.isEmpty(this.$scope[dataKey].values)) { this.$scope[dataKey].empty = true; }
      this.$scope[dataKey].stats = [];
      const denominator = this.$scope[dataKey].max || 1;

      return (() => {
        const result = [];
        for (let key in this.$scope[dataKey].titles) {

          let percentage = (this.$scope[dataKey].values[key] / denominator) * 100;
          if (percentage < 1) { percentage = 1; }

          // case of simple data without sub-data

          if (!this.$scope[dataKey].sub_titles) {

            if (this.$scope[dataKey].values[key]) {
              result.push(this.$scope[dataKey].stats.push({
                title: this.$scope[dataKey].titles[key],
                value: this.$scope[dataKey].values[key] || 0,
                percentage
              }));
            } else {
              result.push(this.$scope[dataKey].stats.push({
                title: this.$scope[dataKey].titles[key]
              }));
            }

          } else {

  // case of more sophisticated case with sub-data

            percentage = (this.$scope[dataKey].group_total[key] / denominator) * 100;
            if (percentage < 1) { percentage = 1; }

            if (this.$scope[dataKey].group_total[key]) {
              const sub_stats = [];

              for (let subid in this.$scope[dataKey].sub_titles) {
                const subtitle = this.$scope[dataKey].sub_titles[subid];
                if (this.$scope[dataKey].values[key][subid]) {
                  let sub_percentage = (this.$scope[dataKey].values[key][subid] / this.$scope[dataKey].group_total[key]) * 100;
                  if (sub_percentage < 1) { sub_percentage = 1; }
                  sub_stats.push({
                    title: subtitle + ' (' + this.$scope[dataKey].values[key][subid] + ')',
                    percentage: sub_percentage,
                    background: this.$scope[dataKey].group_keys[subid]
                  });
                }
              }

              result.push(this.$scope[dataKey].stats.push({
                title: this.$scope[dataKey].titles[key],
                value: this.$scope[dataKey].group_total[key] || 0,
                percentage,
                sub_stats
              }));
            } else {
              result.push(this.$scope[dataKey].stats.push({
                title: this.$scope[dataKey].titles[key]
              }));
            }
          }
        }
        return result;
      })();
    }
  }
  return ReportsOverview;
});