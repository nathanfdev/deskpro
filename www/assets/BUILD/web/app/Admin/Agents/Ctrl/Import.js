define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_Agents_Ctrl_Import extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Agents_Ctrl_Import';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$http', '$upload', 'DpLicense'];
    }

    init() {
      this.busy = false;
      this.restart();
      return this.$scope.fileUploadOptions = { url: this.$http.formatApiUrl('/import_csv_upload') };
    }


    uploadFiles(files) {
      this.busy = true;
      this.page = 1;

      // expected only 1 file
      return Array.from(files).map(file =>
        this.$upload.upload({ url: this.$scope.fileUploadOptions.url, file }).then(
          data => this.sendEmails(data.data.filename),
          () => {
            this.busy = false;
            return console.error('error');
          }));
    }


    restart() {
      this.page = 0; // import page layout number
      this.emails = [];
      this.results = [];
      return this.invited = 0;
    }


    sendEmails(filename) {
      this.busy = true;

      const agents = {};
      this.emails.map(email => agents[email] = { email }); // make unique

      return this.Api.sendPostJson('/agents_bulk/check', { agents, filename }).then((res) => {
        this.busy = false;
        if (res.data.need_plan) {
          return this.DpLicense.openUpgradeLicense('upgrade_plan').then(() => this.doSendEmails(filename));
        }
        return this.doSendEmails(filename);
      }
      , () => this.busy = false);
    }

    doSendEmails(filename) {
      this.busy = true;
      this.page = 1;

      const agents = {};
      this.emails.map(email => agents[email] = { email }); // make unique

      return this.Api.sendPostJson('/agents_bulk', { agents, filename }).then(
        (data) => {
          this.busy = false;

          if (data.data.length != null) { return; } // catch array instead of object, possible if no results

          return (() => {
            const result = [];
            for (const email of Object.keys(data.data || {})) {
              let entry = data.data[email];
              if (entry.person_id != null) { this.invited++; }

              if (entry.error_code === 'validation_error') {
                let message = entry.error_message;
                if ((entry.errors != null ? entry.errors.errors : undefined) != null) {
                  message = '';
                  entry.errors.errors.map(error => message += (`${error.message} `));
                }
                entry = { error: message };
              }

              entry._email = email;
              result.push(this.results.push(entry));
            }
            return result;
          })();
        },

        () => this.busy = false);
    }

    submitEmails() {
      if (!this.$scope.Form.$valid) { return false; }
      return this.sendEmails();
    }
  }
  Admin_Agents_Ctrl_Import.initClass();


  return Admin_Agents_Ctrl_Import.EXPORT_CTRL();
});
