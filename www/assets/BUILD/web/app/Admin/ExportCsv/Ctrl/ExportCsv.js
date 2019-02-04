define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_ExportCsv_Ctrl_ExportCsv extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ExportCsv_Ctrl_ExportCsv';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['Api', '$interval'];
    }


    init() {
      this.$scope.status = null;
      this.$scope.offset = 0;
      this.$scope.files = [];

      this.$scope.start = () => this.Api.sendPost('/export/start').then((data) => {
        this.$scope.status = data.data.status;
        return this.$scope.offset = data.data.offset;
      });

      this.$scope.stop = () => this.Api.sendPost('/export/stop').then((data) => {
        this.$scope.status = data.data.status;
        return this.$scope.offset = data.data.offset;
      });

      return this.$scope.$watch('status', (val) => {
        if ((val === 'running') || (val === 'queued')) {
          if (!this.interval) {
            this.interval = this.$interval(
              () => this.getStatus(),
              5000
            );
          }
        } else if (this.interval) {
          this.$interval.cancel(this.interval);
          this.interval = null;
        }

        if (val === 'completed') { return this.getFiles(); }
      });
    }


    initialLoad() {
      this.getStatus();
      return this.getFiles();
    }


    getStatus() {
      return this.Api.sendGet('/export/status').then((data) => {
        this.$scope.status = data.data != null ? data.data.status : undefined;
        return this.$scope.offset = data.data != null ? data.data.offset : undefined;
      });
    }


    getFiles() {
      return this.Api.sendGet('/export/list').then(data => this.$scope.files = data.data || []);
    }
  }
  Admin_ExportCsv_Ctrl_ExportCsv.initClass();


  return Admin_ExportCsv_Ctrl_ExportCsv.EXPORT_CTRL();
});
