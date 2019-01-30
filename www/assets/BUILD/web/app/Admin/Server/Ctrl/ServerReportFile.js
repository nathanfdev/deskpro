// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerReportFile_Ctrl_ServerReportFile extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerReportFile_Ctrl_ServerReportFile';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$window'];
    }

    init() {
      this.server_file_check = null;

      this.total_checks = 0;
      this.current_check =
      (this.current_percentage = 0);

      this.check_started = false;
      this.check_in_progress = false;

      this.file_check_results = '';
      this.server_file_check_done = false;
      return this.$scope.with_file_check = true;
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
      this.file_check_results = '';
      return this.doNextRequest();
    }

    /*
     * Execute AJAX request to next batch of files
     */
    doNextRequest() {
      this.current_check++;

      if ((this.current_check <= this.total_checks) && this.$scope.with_file_check && (this.file_check_results.length < 153600)) {
        return this.Api.sendGet(`/server_file_check/${this.current_check-1}`).then(res => {
          let file;
          const data = res.data.server_file_check;

          if (data.okay) {
            this.file_check_results += `Batch ${this.current_check} of ${this.total_checks}: ${data.okay.length} files verified\n`;
          }

          if (data.added) {
            for (file of Array.from(data.added)) {
              this.file_check_results += `Batch ${this.current_check} of ${this.total_checks}:  File added: ${file}\n`;
            }
          }

          if (data.changed && data.changed.length) {
            for (file of Array.from(data.changed)) {
              this.file_check_results += `Batch ${this.current_check} of ${this.total_checks}:  File changed: ${file}\n`;
            }
          }

          if (data.removed && data.removed.length) {
            for (file of Array.from(data.removed)) {
              this.file_check_results += `Batch ${this.current_check} of ${this.total_checks}:  Missing: ${file}\n`;
            }
          }

          this.current_percentage = Math.ceil((this.current_check / this.total_checks) * 100);

          return this.doNextRequest();
        });
      } else {
        if (this.file_check_results >= 153600) {
          this.file_check_results = this.file_check_results.substring(0, 153600) + "\n\n(Too many changes detected, results truncated)";
        }

        this.check_in_progress = false;
        this.current_percentage = 100;
        this.server_file_check_done = true;
        return this.redirectToReportFile();
      }
    }

    /*
     * After we've donw with file integrity checking we coudl redirect user to actual report file
     */
    redirectToReportFile() {
      return this.Api.sendPost('/server_report_file/file_check_results', {file_check_results: this.file_check_results}).then( res => {
        return this.$scope.download_link = window.DP_BASE_API_URL + '/server_report_file?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN;
      });
    }
  }
  Admin_ServerReportFile_Ctrl_ServerReportFile.initClass();

  return Admin_ServerReportFile_Ctrl_ServerReportFile.EXPORT_CTRL();
});