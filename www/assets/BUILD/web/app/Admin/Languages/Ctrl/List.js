define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Languages_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      this.$scope.isInstalled = pack => pack.is_installed;
      this.$scope.notInstalled = pack => !pack.is_installed;
      this.default_lang_id = null;

      return this.$scope.$watch('ListCtrl.default_lang_id', (oldVal, newVal) => {
        if (oldVal && newVal && (parseInt(oldVal) !== parseInt(newVal))) {
          return this.updateDefaultLang();
        }
      });
    }

    initialLoad() {
      const promise = this.Api.sendGet('/langs').then( result => {
        this.packs = result.data.packs;
        this.installedPacks = [];
        this.availablePacks = [];
        this.default_lang_id = result.data.default_lang_id;

        return this.resortPacks();
      });
      return promise;
    }

    /*
      * Fetch a pack from its packId
      *
      * @param {String} packId
      * @return {Object}
      */
    _getPackByPackId(packId) {
      for (let pack of Array.from(this.packs)) {
        if (pack.id === packId) {
          return pack;
        }
      }
      return null;
    }


    /*
     * Resort packs into installed/available lists
       */
    resortPacks() {
      this.installedPacks = [];
      this.availablePacks = [];

      return (() => {
        const result = [];
        for (let pack of Array.from(this.packs)) {
          pack.flag_image = DP_ASSET_URL + '/images/flags/' + pack.show_flag;

          if (pack.is_installed) {
            result.push(this.installedPacks.push(pack));
          } else {
            result.push(this.availablePacks.push(pack));
          }
        }
        return result;
      })();
    }


    /*
      * Install a language by pack_id
      *
    * @return promise
    */
    installLang(pack_id) {
      const promise = this.Api.sendPost(`/langs/${pack_id}/install`).then( result => {
        const pack = this._getPackByPackId(result.data.pack_id);
        pack.is_installed = true;
        return this.resortPacks();
      });

      return promise;
    }


    /*
      * Uninstall a language by language_id or pack_id
      *
      * @return promise
    */
    uninstallLang(id) {
      const promise = this.Api.sendPost(`/langs/${id}/uninstall`).then( result => {
        const pack = this._getPackByPackId(result.data.old_pack_id);
        pack.is_installed = false;
        return this.resortPacks();
      });

      return promise;
    }

    /*
      * Save a language
      *
      * @return promise
    */
    saveLanguage(id, details) {
      const pack = this._getPackByPackId(id);

      const postData = {
        language: details
      };

      const promise = this.Api.sendPostJson(`/langs/${id}`, postData).then( result => {
        pack.show_title = details.title;
        pack.locale     = details.locale;
        pack.show_flag  = details.flag_image;
        return this.resortPacks();
      });

      return promise;
    }

    updateDefaultLang() {
      if (this.default_lang_id && this.hasLoaded()) {
        this.startSpinner('saving_default_lang');
        return this.Api.sendPost(`/langs/${this.default_lang_id}/set-default`).then(() => {
          return this.stopSpinner('saving_default_lang');
        });
      }
    }
  }
  Admin_Languages_Ctrl_List.initClass();


  return Admin_Languages_Ctrl_List.EXPORT_CTRL();
});