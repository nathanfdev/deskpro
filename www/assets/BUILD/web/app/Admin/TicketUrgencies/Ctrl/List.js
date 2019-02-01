define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketUrgencies_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketUrgencies_Ctrl_List';
      this.CTRL_AS = 'TicketUrgenciesList';
      this.DEPS = [];
    }

    init() {
      this.urgency_counts = {};
      this.urgencies = [];
    }

    initialLoad() {
      const promise = this.Api.sendGet("/ticket_urgencies").success( data => {
        this.urgency_counts = data.urgency_counts;

        return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((num) =>
          this.urgencies.push({
            num,
            ticket_count: this.urgency_counts[num] ? this.urgency_counts[num] : 0
          }));
      });

      return promise;
    }
  }
  Admin_TicketUrgencies_Ctrl_List.initClass();

  return Admin_TicketUrgencies_Ctrl_List.EXPORT_CTRL();
});