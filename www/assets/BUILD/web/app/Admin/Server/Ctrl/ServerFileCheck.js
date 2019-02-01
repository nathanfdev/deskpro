define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerFileCheck_Ctrl_ServerFileCheck extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerFileCheck_Ctrl_ServerFileCheck';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = [];
    }

    init() {
      this.server_file_check = null;

      this.total_checks = 0;
      this.current_check = 0;
      this.current_percentage = 0;

      this.check_started = false;
      this.check_in_progress = false;
      this.has_errors = false;

      this.show_log = false;
      this.show_log_text = 'Show Log';
      this.logs = [];
      return this.error_logs = [];
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_file_check').then( res => {
        this.server_file_check = res.data.server_file_check;
        this.total_checks = this.server_file_check.count;

        // the case when we have '/app/sys/Resources/distro-checksums.php' deleted
        if (this.total_checks === 1) {
          this.current_check = -1;
          return this.doNextRequest();
        }
      });

      return this.$q.all([data_promise]);
    }


    /*
     * Starting the process of integrity file check
     */
    startCheck() {
      this.current_check = 0;
      this.current_percentage = 0;
      this.check_started = true;
      this.check_in_progress = true;
      this.has_errors = false;
      this.logs = [];
      this.error_logs = [];

      return this.doNextRequest();
    }


    /*
     * Execute AJAX request to next batch of files
     */
    doNextRequest() {
      this.current_check++;
      return this.Api.sendGet(`/server_file_check/${this.current_check - 1}`).then(res => {
        let file;
        const data = res.data.server_file_check;

        if (data.okay) {
          this.logs.push(`Batch ${this.current_check} of ${this.total_checks}: ${data.okay.length} files verified`);
        }
        if (data.added) {
          for (file of Array.from(data.added)) {
            this.logs.push(`Batch ${this.current_check} of ${this.total_checks}:  File added: ${file}`);
          }
        }

        if (data.changed && data.changed.length) {
          for (file of Array.from(data.changed)) {
            this.logs.push(`Batch ${this.current_check} of ${this.total_checks}:  File changed: ${file}`);
            this.error_logs.push(`CHANGED: ${file}`);
            this.has_errors = true;
          }
        }
        if (data.removed && data.removed.length) {
          for (file of Array.from(data.removed)) {
            this.logs.push(`Batch ${this.current_check} of ${this.total_checks}:  Missing: ${file}`);
            this.error_logs.push(`MISSING: ${file}`);
            this.has_errors = true;
          }
        }

        this.current_percentage = Math.ceil((this.current_check / this.total_checks) * 100);

        if (this.current_check < this.total_checks) {
          return this.doNextRequest();
        } else {
          this.check_in_progress = false;
          return this.current_percentage = 100;
        }
      });
    }

    /*
     * Show / hide 'show log' button
     */
    toggleLog() {
      this.show_log = !this.show_log;
      return this.show_log_text = this.show_log ? 'Hide Log' : 'Show Log';
    }
  }
  Admin_ServerFileCheck_Ctrl_ServerFileCheck.initClass();


  return Admin_ServerFileCheck_Ctrl_ServerFileCheck.EXPORT_CTRL();
});