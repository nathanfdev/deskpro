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
  class Admin_Languages_Ctrl_Settings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_Settings';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = ['$http', 'LangSyncApi'];
    }

    init() {
      this.form = {
        lang_auto_install: false,
        lang_auto_detect:  false,
        tickets_move_from: 0,
        tickets_move_to:   0,
        users_move_from:   0,
        users_move_to:     0,
        download_language: 'all'
      };
      return this.syncLog = '';
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        auto_install: '/settings/values/core.lang_auto_install',
        auto_detect:  '/settings/values/core.lang_auto_detect',
        lang:         '/langs'
      }).then( res => {
        this.form.lang_auto_install = parseInt(res.data.auto_install.value) ? true : false;
        this.form.lang_auto_detect = parseInt(res.data.auto_detect.value) ? true : false;

        this.langChoices  = [];
        for (let pack of Array.from(res.data.lang.packs)) {
          if (pack.is_installed) {
            this.langChoices.push({
              id:          pack.installed_language_id,
              system_name: pack.id,
              title:       pack.title
            });
          }
        }

        this.form.tickets_move_to = res.data.lang.default_lang_id;
        return this.form.users_move_to = res.data.lang.default_lang_id;
      });
      return promise;
    }

    saveSettings() {
      if (this.$scope.form_props && this.$scope.form_props.$invalid) { return; }

      this.startSpinner('saving_settings');
      const promises = [];
      promises.push(this.Api.sendPost('/settings/values/core.lang_auto_install', {
        value: this.form.lang_auto_install ? '1' : '0'
      })
      );
      promises.push(this.Api.sendPost('/settings/values/core.lang_auto_detect', {
        value: this.form.lang_auto_detect ? '1' : '0'
      })
      );

      return this.$q.all(promises).then(() => {
        return this.stopSpinner('saving_settings');
      });
    }

    doMassTicketMove() {
      return this.showConfirm('@confirm_move_tickets').result.then(() => {
        this.startSpinner('saving_tickets');
        const postData = {
          from_lang: this.form.tickets_move_from,
          to_lang:   this.form.tickets_move_to
        };
        return this.Api.sendPost('/langs/tools/mass-update-tickets', postData).then(() => {
          return this.stopSpinner('saving_tickets');
        });
      });
    }

    doMassUserMove() {
      return this.showConfirm('@confirm_move_users').result.then(() => {
        this.startSpinner('saving_users');
        const postData = {
          from_lang: this.form.users_move_from,
          to_lang:   this.form.users_move_to
        };
        return this.Api.sendPost('/langs/tools/mass-update-users', postData).then(() => {
          return this.stopSpinner('saving_users');
        });
      });
    }

    doDownloadLanguages() {

      const syncLanguage = (nameId, locale) => {
        console.log('[Language sync] Process: ', locale);
        this.syncLog += `Downloading ${locale} ...\n`;
        return this.$q.all([
          this.LangSyncApi.getPhrases(locale, 'backend'),
          this.LangSyncApi.getPhrases(locale, 'user')
        ]).then(res => {
          //combine user and backend phrases
          const postData = {
            phrases: Object.assign({}, res[0].data, res[1].data)
          };

          this.syncLog += `Syncing ${locale} ...\n`;
          return this.Api.sendPostJson(`/langs/${nameId}/phrases/sync`, postData);
        });
      };

      return this.showConfirm('@confirm_download_languages').result.then(() => {
        this.startSpinner('update_languages');

        this.syncLog = '';
        this.showLog = true;

        this.syncLog += "Downloading Manifest ...\n";
        return this.LangSyncApi.getManifest().then(result => {
          const manifest = result.data;
          let langs = this.langChoices;
          if (this.form.download_language !== 'all') {
            langs = [{system_name: this.form.download_language}];
          }

          langs = langs.map(l => l.system_name);

          const deferred = this.$q.defer();
          let res = deferred.promise;

          //queue promises to process languages sync one by one
          manifest.forEach(function(lang) {
            if (langs.indexOf(lang.id) !== -1) {
              return res = res.then(() => syncLanguage(lang.id, lang.locale));
            }
          });

          deferred.resolve();

          //execute chained promises
          return res
            .then(() => {
              this.stopSpinner('update_languages');
              return this.syncLog += "Done.\n";
            })
            .catch(error => {
              console.log(error);
              this.syncLog += "Error. Please check the console.\n";
              return this.stopSpinner('update_languages');
            });
        }).catch( error => {
          console.log(error);
          this.syncLog += "Error. Please check the console.\n";
          return this.stopSpinner('update_languages');
        });
      });
    }

    doResetManagedPhrases() {

      return this.showConfirm('@confirm_reset_managed_phrases').result.then(() => {
        this.startSpinner('update_languages');
        return this.Api.sendPostJson("/langs/phrases/reset-managed", {}).then( result => {
          return this.stopSpinner('update_languages');
        });
      });
    }
  }
  Admin_Languages_Ctrl_Settings.initClass();

  return Admin_Languages_Ctrl_Settings.EXPORT_CTRL();
});