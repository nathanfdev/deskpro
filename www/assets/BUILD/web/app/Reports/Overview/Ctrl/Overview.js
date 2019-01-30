// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/Main/Ctrl/Base',
  'DeskPRO/Util/Util',
], function(
  ReportsBaseCtrl,
  Util,
) {
  class Reports_Overview_Ctrl_Overview extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_Overview_Ctrl_Overview';
      this.CTRL_AS   = 'Overview';
    }

    init() {
      this.$scope.agent_team  = 0;
      return this.$scope.agent_teams = [];
    }

    /*
     * Just doing all the necessary AJAX calls here
     */
    initialLoad() {
      this.$scope.getStats = this.getStats;
      const agentTeamsPromise = this.Api2.sendGet('/agent_teams').then( res => {
        const teams = res.data.data;
        if (teams.length) {
          teams.unshift({ id: 0, name: 'All' });
        }

        return this.$scope.agent_teams = teams;
      });

      return this.$q.all([this.loadData(), agentTeamsPromise]);
    }

    loadData() {
      const queryParams = `?${encodeURIComponent('agent_team')}=${encodeURIComponent(this.$scope.agent_team)}`;

      return this.Api.sendDataGet({
        tickets_status:            `/reports/overview/data/tickets_status${queryParams}`,
        tickets_awaiting_agent:    `/reports/overview/data/tickets_awaiting_agent${queryParams}`,
        tickets_user_waiting_time: `/reports/overview/data/tickets_user_waiting_time${queryParams}`,
        tickets_resolved:          `/reports/overview/data/tickets_resolved${queryParams}`,
        tickets_response_time:     `/reports/overview/data/tickets_response_time${queryParams}`,
        tickets_sla_status:        `/reports/overview/data/tickets_sla_status${queryParams}`,
        tickets_opened_hour:       `/reports/overview/data/tickets_opened_hour${queryParams}`,
        chats_created:             `/reports/overview/data/chats_created${queryParams}`
      }).then( res => {
        this.$scope.tickets_status            = res.data.tickets_status;
        this.$scope.tickets_awaiting_agent    = res.data.tickets_awaiting_agent;
        this.$scope.tickets_user_waiting_time = res.data.tickets_user_waiting_time;
        this.$scope.tickets_resolved          = res.data.tickets_resolved;
        this.$scope.tickets_response_time     = res.data.tickets_response_time;
        this.$scope.tickets_sla_status        = res.data.tickets_sla_status;
        this.$scope.tickets_opened_hour       = res.data.tickets_opened_hour;
        this.$scope.chats_created             = res.data.chats_created;

        this.setDataForBarGraphs('tickets_status');
        this.setDataForBarGraphs('tickets_awaiting_agent');
        this.setDataForBarGraphs('tickets_resolved');
        this.setDataForBarGraphs('tickets_sla_status');
        this.setDataForBarGraphs('chats_created');

        this.setDataForTicketsOpenedHours();

        this.setDataForTableWithBarGraphs('tickets_user_waiting_time');
        return this.setDataForTableWithBarGraphs('tickets_response_time');
      });
    }


    /*
     * This method is used in select boxes for defining grouping field and / or other search parameters
     * @param {String} data_key - using this key data is looked in @$scope
     */
    getStats(data_key) {
      this.toggleLoadingState(data_key);

      const promise = this.Api.sendGet(`/reports/overview/get-stats/${data_key}`, {
        grouping_field: this.$scope[data_key].grouping_field,
        date_choice: this.$scope[data_key].date_choice,
        sla_id: this.$scope[data_key].sla_id
      });

      return promise.success(data => {
        this.toggleLoadingState(data_key);
        this.$scope[data_key] = data;

        if ((data_key === 'tickets_user_waiting_time') || (data_key === 'tickets_response_time')) {
          return this.setDataForTableWithBarGraphs(data_key);
        } else if (data_key === 'tickets_opened_hour') {
          return this.setDataForTicketsOpenedHours();
        } else {
          return this.setDataForBarGraphs(data_key);
        }
      });
    }



    /*
     * Used for hiding / showing AJAX loader
     */
    toggleLoadingState(data_key) {
      return this.$scope[data_key].loading = !this.$scope[data_key].loading;
    }


    /*
     * We need to display bar graphs - so let's pre-calculate some variables
     * @param {String} data_key - using this key data is looked in @$scope
     */
    setDataForBarGraphs(data_key) {
      if (Util.isEmpty(this.$scope[data_key].values)) { this.$scope[data_key].empty = true; }
      this.$scope[data_key].stats = [];
      const denominator = this.$scope[data_key].max || 1;

      return (() => {
        const result = [];
        for (let key in this.$scope[data_key].titles) {

          if (this.$scope[data_key].values[key]) {
            let percentage = (this.$scope[data_key].values[key] / denominator) * 100;
            if (percentage < 1) { percentage = 1; }

            result.push(this.$scope[data_key].stats.push({
              title: this.$scope[data_key].titles[key],
              value: this.$scope[data_key].values[key] || 0,
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
     * @param {String} data_key - using this key data is looked in @$scope
     */
    setDataForTableWithBarGraphs(data_key) {
      if (Util.isEmpty(this.$scope[data_key].values)) { this.$scope[data_key].empty = true; }
      this.$scope[data_key].stats = [];
      const denominator = this.$scope[data_key].max || 1;

      return (() => {
        const result = [];
        for (let key in this.$scope[data_key].titles) {

          let percentage = (this.$scope[data_key].values[key] / denominator) * 100;
          if (percentage < 1) { percentage = 1; }

          // case of simple data without sub-data

          if (!this.$scope[data_key].sub_titles) {

            if (this.$scope[data_key].values[key]) {
              result.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key],
                value: this.$scope[data_key].values[key] || 0,
                percentage
              }));
            } else {
              result.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key]
              }));
            }

          } else {

            // case of more sophisticated case with sub-data

            percentage = (this.$scope[data_key].group_total[key] / denominator) * 100;
            if (percentage < 1) { percentage = 1; }

            if (this.$scope[data_key].group_total[key]) {
              const sub_stats = [];

              for (let subid in this.$scope[data_key].sub_titles) {
                const subtitle = this.$scope[data_key].sub_titles[subid];
                if (this.$scope[data_key].values[key][subid]) {
                  let sub_percentage = (this.$scope[data_key].values[key][subid] / this.$scope[data_key].group_total[key]) * 100;
                  if (sub_percentage < 1) { sub_percentage = 1; }
                  sub_stats.push({
                    title: subtitle + ' (' + this.$scope[data_key].values[key][subid] + ')',
                    percentage: sub_percentage,
                    background: this.$scope[data_key].group_keys[subid]
                  });
                }
              }

              result.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key],
                value: this.$scope[data_key].group_total[key] || 0,
                percentage,
                sub_stats
              }));
            } else {
              result.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key]
              }));
            }
          }
        }
        return result;
      })();
    }
  }
  Reports_Overview_Ctrl_Overview.initClass();

  return Reports_Overview_Ctrl_Overview.EXPORT_CTRL();
});