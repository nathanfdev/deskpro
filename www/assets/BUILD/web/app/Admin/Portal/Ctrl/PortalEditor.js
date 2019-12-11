define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class AdminPortalCtrlPortalEditor extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        const thisFn = (() => this).toString();
        const thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }

      super(...args);

      this.init = this.init.bind(this);
      this.save = this.save.bind(this);
      this.editTheme = this.editTheme.bind(this);
      this.saveWelcomeBox = this.saveWelcomeBox.bind(this);
      this.clearWelcomeBox = this.clearWelcomeBox.bind(this);
      this.editWelcomeBox = this.editWelcomeBox.bind(this);
      this.saveThemeOptions = this.saveThemeOptions.bind(this);
      this.editThemeOptions = this.editThemeOptions.bind(this);
      this.saveValues = this.saveValues.bind(this);
      this.commit = this.commit.bind(this);
      this.discard = this.discard.bind(this);
      this.initialLoad = this.initialLoad.bind(this);
      this.togglePanel = this.togglePanel.bind(this);
      this.isOpen = this.isOpen.bind(this);
      this.label = this.label.bind(this);
      this.refreshPreviewUrl = this.refreshPreviewUrl.bind(this);
      this.loadValues = this.loadValues.bind(this);
      this.loadTemplateOptions = this.loadTemplateOptions.bind(this);
      this.templateName = this.templateName.bind(this);
      this.templateGroup = this.templateGroup.bind(this);
      this.editTemplate = this.editTemplate.bind(this);
      this.openTemplateEditor = this.openTemplateEditor.bind(this);
      this.saveTemplateEditor = this.saveTemplateEditor.bind(this);
      this.revertTemplateEditor = this.revertTemplateEditor.bind(this);
      this.openCssEditor = this.openCssEditor.bind(this);
      this.cancelCssEditor = this.cancelCssEditor.bind(this);
      this.saveCssEditor = this.saveCssEditor.bind(this);
      this.resetCssEditor = this.resetCssEditor.bind(this);
      this.cancelTemplateEditor = this.cancelTemplateEditor.bind(this);
      this.loadAdvancedEdits = this.loadAdvancedEdits.bind(this);
      this.loadAssetFiles = this.loadAssetFiles.bind(this);
      this.loadThemeSet = this.loadThemeSet.bind(this);
      this.loadWelcomeBox = this.loadWelcomeBox.bind(this);
      this.loadLogo = this.loadLogo.bind(this);
      this.loadFavicon = this.loadFavicon.bind(this);
      this.loadSplashImage = this.loadSplashImage.bind(this);
      this.upload = this.upload.bind(this);
      this.uploadLogo = this.uploadLogo.bind(this);
      this.uploadFavicon = this.uploadFavicon.bind(this);
      this.uploadSplashImage = this.uploadSplashImage.bind(this);
      this.copyUrl = this.copyUrl.bind(this);
      this.isDirtyState = this.isDirtyState.bind(this);
      this.notifyUrlCopied = this.notifyUrlCopied.bind(this);
      this.delete = this.delete.bind(this);
      this.deleteLogo = this.deleteLogo.bind(this);
      this.deleteFavicon = this.deleteFavicon.bind(this);
      this.deleteSplashImage = this.deleteSplashImage.bind(this);
      this.openAdvancedTab = this.openAdvancedTab.bind(this);
      this.isAdvancedTab = this.isAdvancedTab.bind(this);
      this.isAdvancedExpanded = this.isAdvancedExpanded.bind(this);
      this.collapseAdvanced = this.collapseAdvanced.bind(this);
      this.expandAdvanced = this.expandAdvanced.bind(this);
      this.canPreview = this.canPreview.bind(this);
      this.previewAs = this.previewAs.bind(this);
      this.promptEmail = this.promptEmail.bind(this);
      this.error = this.error.bind(this);
      this.success = this.success.bind(this);
      this.serverError = this.serverError.bind(this);
      this.cloneTheme = this.cloneTheme.bind(this);
      this.importTheme = this.importTheme.bind(this);
      this.importAndReplaceTheme = this.importAndReplaceTheme.bind(this);
    }

    static initClass() {
      this.CTRL_ID = 'AdminPortalCtrlPortalEditor';
      this.CTRL_AS = 'Portal';
      this.DEPS    = ['$http', '$scope', '$timeout', '$upload', '$modal', 'Growl'];
    }

    init() {
      this.open_panels = ['theme', 'colors', 'advanced', 'expert'];
      this.recompiling = false;
      this.commiting = false;
      this.savingMulti = false;
      this.advanced = { main_scss: '', custom_scss: '', javascript: '' };
      this.available_themes = [
        { id: 'standard', title: 'Standard' },
        { id: 'sidebar', title: 'Sidebar' },
        { id: 'helpcenter', title: 'HelpCenter' }
      ];
      this.$scope.brand_id = this.$stateParams.brandId;

      this.$scope.welcome_box = {
        title:   '',
        message: ''
      };
      this.welcome_box = angular.copy(this.$scope.welcome_box);
      this.$scope.theme_options = {
        show_section_navigation: true,
      };
      this.theme_options = angular.copy(this.$scope.theme_options);

      this.$scope.values = {};
      this.$scope.errors = {
        favicon:      false,
        logo:         false,
        splash_image: false
      };

      this.values = angular.copy(this.$scope.values);

      this.advanced_tab = 'header';
      this.is_advanced_expanded = false;
      this.asset_files = [];
      this.custom_logo = null;
      this.custom_favicon = null;
      this.uploading_files_count = 0;
      this.template_options = [];
      this.selected_template = null;
      this.selected_template_info = {};
      this.selected_template_info_loaded = false;
      this.css_template_info = null;
      this.css_template_selected = null;
      this.preview_as_expanded = false;
      this.preview_as = 'myself';
      this.preview_as_email = null;
      this.selected_theme = null;
      this.theme_set = null;
    }

    save() {
      const promises = [this.saveValues(), this.editWelcomeBox(), this.editThemeOptions()];
      this.savingMulti = true;
      const all = this.$q.all(promises);
      return all.then(() => this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/advanced-edits`,
        data:   this.advanced
      }).then(() => {
        this.savingMulti = false;
        return this.refreshPreviewUrl();
      }
        , () => {
          this.serverError();
          return this.savingMulti = false;
        })
      , () => {
        this.serverError();
        return this.savingMulti = false;
      });
    }

    editTheme() {
      console.log('editTheme');
      const request = this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/info`,
        data:   {
          id: this.selected_theme
        }
      });
      this.recompiling = true;
      return request.then(
        () => {
          this.loadGroups();
          this.refreshPreviewUrl();
          return this.recompiling = false;
        },
        () => {
          this.serverError(); return this.recompiling = false;
        });
    }

    saveWelcomeBox() {
      return this.editWelcomeBox().then(
        () => {
          if (!this.savingMulti) { return this.refreshPreviewUrl(); }
        });
    }

    clearWelcomeBox() {
      this.welcome_box = { title: '', message: '' };
      return this.saveWelcomeBox();
    }

    editWelcomeBox() {
      const request = this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/welcome-message`,
        data:   this.$scope.welcome_box
      });
      this.recompiling = true;
      return request.then(
        () => { this.recompiling = false; return this.welcome_box = angular.copy(this.$scope.welcome_box); },
        () => {
          this.serverError(); return this.recompiling = false;
        });
    }

    saveThemeOptions() {
      return this.editThemeOptions().then(
        () => {
          if (!this.savingMulti) { return this.refreshPreviewUrl(); }
        });
    }

    editThemeOptions() {
      const request = this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/theme-options`,
        data:   this.$scope.theme_options
      });
      this.recompiling = true;
      return request.then(
        () => { this.recompiling = false; return this.theme_options = angular.copy(this.$scope.theme_options); },
        () => {
          this.serverError(); return this.recompiling = false;
        });
    }

    saveValues() {
      const request = this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/variable-values`,
        data:   this.$scope.values
      });
      this.recompiling = true;
      request.then(
        () => { this.recompiling = false; return this.values = angular.copy(this.$scope.values); },
        (message) => {
          this.recompiling = false; return this.serverError(message);
        });
      return request;
    }

    commit() {
      return this.showConfirm('Are you sure you want to apply this changes to the portal?', 'Confirm save').result.then(
        () => {
          let promises;
          this.commiting = true;
          if (this.isDirtyState()) {
            promises = [this.saveValues(), this.editWelcomeBox(), this.editThemeOptions()];
          } else {
            promises = [true];
          }

          const all = this.$q.all(promises);
          return all.then(
            () => this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/commit`).then(
              () => { this.success('Changes were applied to the portal'); return this.commiting = false; },
              () => { this.serverError(); return this.commiting = false; }),
            () => this.commiting = false);
        });
    }

    discard() {
      return this.showConfirm('Are you sure you want to discard all changes you\'ve made?', 'Confirm discard').result.then(
        () => {
          this.recompiling = true;
          const request = this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/discard`).success((response) => {
            return this.selected_theme = response.id;
          });
          const promises = [request, this.loadAdvancedEdits(), this.loadLogo(), this.loadFavicon(), this.loadValues(), this.loadGroups()];
          const all = this.$q.all(promises);
          return all.then(
            () => new Promise(resolve => resolve(this.refreshPreviewUrl())).then(() => { this.success('Changes were discarded'); return this.recompiling = false; }),
            () => {
              this.serverError(); return this.recompiling = false;
            });
        });
    }

    initialLoad() {
      const d = this.$q.defer();
      this.Api2.sendGet(`brands/${this.$scope.brand_id}`).then((res) => {
        this.$scope.baseUrl  = `${window.DP_BASE_URL}b/${res.data.data.slug}`;
        this.refreshPreviewUrl();

        return this.$q.all([
          this.loadGroups(),
          this.loadValues(),
          this.loadAdvancedEdits(),
          this.loadAssetFiles(),
          this.loadLogo(),
          this.loadFavicon(),
          this.loadSplashImage(),
          this.loadThemeOptions(),
          this.loadTemplateOptions(),
          this.loadCustomThemeSets(),
          this.loadThemeSet(),
          this.loadWelcomeBox()
        ]).then(() => d.resolve());
      });

      return d.promise;
    }

    togglePanel(name) {
      if (Array.from(this.open_panels).includes(name)) {
        return this.open_panels = this.open_panels.filter(e => e !== name);
      }
      return this.open_panels.push(name);
    }

    isOpen(name) {
      return Array.from(this.open_panels).includes(name);
    }

    label(sys_name) {
      return sys_name.replace(/[\-_]/g, ' ').replace(/^(.)|\s(.)/g, v => v.toUpperCase());
    }

    refreshPreviewUrl() {
      let preview_url = `${window.DP_BASE_URL}admin-preview-${this.$scope.brand_id}?anti-cache=${(new Date()).getTime()}`;
      if ((this.preview_as === 'user') || (this.preview_as === 'agent')) { preview_url += `&_preview_as=${this.preview_as_email}`; }
      if (this.preview_as === 'myself') { preview_url += '&_preview_as=_exit'; }
      if (this.preview_as === 'guest') { preview_url += '&_preview_as=_anon'; }
      return this.preview_url = preview_url;
    }

    loadGroups() {
      console.log('loadGroups');
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/variable-groups`).success(
        data => this.groups = data
      )
    }

    loadValues(success) {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/variable-values`).success(
        (values) => {
          angular.extend(this.$scope.values, values);
          this.values = angular.copy(this.$scope.values);

          if (success) {
            return success();
          }
        });
    }

    loadTemplateOptions() {
      const template_options = [];
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/templates`).success(
        (templates) => {
          for (const template of Array.from(templates)) {
            template_options.push({
              value:  template.name,
              custom: template.is_custom,
              name:   this.templateName(template),
              group:  this.templateGroup(template)
            });
          }
          return this.template_options = template_options;
        });
    }

    templateName(template) {
      let name = template.name.split(':')[2].replace(/\.twig/, '');
      if (template.is_custom) { name = `(*)${name}`; }
      return name;
    }
    templateGroup(template) {
      const parts = template.name.split(':');
      if (parts[1]) { return parts[1]; }  return parts[0];
    }

    editTemplate() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/template-info?template=${this.selected_template}`).success((data) => {
        this.selected_template_info = {
          code:      data.source,
          is_custom: data.is_custom
        };
        return this.selected_template_info_loaded = true;
      });
    }

    openTemplateEditor(tpl) {
      this.selected_template = tpl;
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/template-info?template=${this.selected_template}`).success((data) => {
        this.selected_template_info = {
          code:      data.source,
          is_custom: data.is_custom
        };
        return this.selected_template_info_loaded = true;
      });
    }

    saveTemplateEditor() {
      return this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/template-sources?template=${this.selected_template}`,
        data:   angular.toJson({ code: this.selected_template_info.code })
      })
      .success(
        () => {
          this.selected_template = null;
          this.selected_template_info_loaded = false;
          return this.loadTemplateOptions();
        })
      .error(this.serverError);
    }

    revertTemplateEditor() {
      this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/template-sources?template=${this.selected_template}`,
        data:   angular.toJson({ revert: true })
      })
      .error(this.serverError);

      this.selected_template = null;
      return this.selected_template_info_loaded = false;
    }

    openCssEditor(type) {
      this.css_template_selected = true;
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/advanced-edits`).success(data => this.css_template_info = {
        loaded:    true,
        type,
        code:      data[type],
        is_custom: true
      });
    }

    cancelCssEditor() {
      this.css_template_selected = false;
      return this.css_template_info = false;
    }

    saveCssEditor() {
      const data = {};
      data[this.css_template_info.type] = this.css_template_info.code;
      this.advanced[this.css_template_info.type] = this.css_template_info.code;
      this.recompiling = true;

      const req = this.$http({
        method: 'PUT',
        url:    `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/advanced-edits`,
        data:   angular.toJson(data)
      })
      .success(() => this.recompiling = false)
      .error((message) => { this.recompiling = false; return this.serverError(message); });

      this.css_template_selected = null;
      this.css_template_info = false;

      return req;
    }

    resetCssEditor() {
      const data = {};
      data[this.css_template_info.type] = true;
      const req = this.$http({
        method:  'DELETE',
        url:     `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/advanced-edits`,
        data:    angular.toJson(data),
        headers: {
          'Content-Type': 'application/json'
        }
      })
      .success(() => this.recompiling = false)
      .error((message) => { this.recompiling = false; return this.serverError(message); });

      this.css_template_selected = null;
      this.css_template_info = false;
      return req;
    }

    cancelTemplateEditor() {
      this.selected_template = null;
      return this.selected_template_info_loaded = false;
    }

    loadAdvancedEdits(success) {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/advanced-edits`).success(
        (advanced) => {
          angular.extend(this.advanced, advanced);
          if (success) {
            return success();
          }
        });
    }

    loadAssetFiles() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/assets`).success(
        response => angular.extend(this.asset_files, response.data)
      );
    }

    loadCustomThemeSets() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/custom-theme-sets`).success((data) => {
        data.forEach((themeSet) => {
          this.available_themes.push(themeSet);
        });
      });
    }

    loadThemeSet() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/info`).success((data) => {
        this.theme_set = data;
        return this.selected_theme = this.theme_set.theme_id;
      });
    }

    loadWelcomeBox() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/welcome-message`).success((response) => {
        this.$scope.welcome_box = response.data;
        return this.welcome_box = angular.copy(this.$scope.welcome_box);
      });
    }

    loadLogo() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/logo`).success(response => this.custom_logo = response.data != null ? response.data.url : undefined);
    }

    loadFavicon() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/favicon`).success(response => this.custom_favicon = response.data != null ? response.data.url : undefined);
    }
    loadSplashImage() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/splash_image`).success(response => this.custom_splash_image = response.data != null ? response.data.url : undefined);
    }

    loadThemeOptions() {
      return this.$http.get(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/theme_options`).success((response) => {
        this.$scope.theme_options = response.data;
        return this.theme_options = angular.copy(this.$scope.theme_options);
      });
    }

    upload(files) {
      return (() => {
        const result = [];
        for (const file of Array.from(files)) {
          this.uploading_files_count++;
          result.push(this.$upload.upload({
            url: `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/assets`,
            file
          }).then(
            (response) => {
              this.uploading_files_count--;
              return this.asset_files.unshift(response.data.data);
            }
            ,
            () => this.error('Server error occurred. Unable to upload files.')
          ));
        }
        return result;
      })();
    }

    uploadLogo(files) {
      return this.$upload
        .upload({ url: `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/logo`, file: files[0] })
        .then(
          (response) => {
            this.$scope.errors.logo = false;
            return this.custom_logo = response.data.data.url;
          },
          response => this.$scope.errors.logo = response.data.fields.file.errors[0].message);
    }
    uploadFavicon(files) {
      return this.$upload
        .upload({ url: `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/favicon`, file: files[0] })
        .then(
          (response) => {
            this.$scope.errors.favicon = false;
            return this.custom_favicon = response.data.data.url;
          },
          response => this.$scope.errors.favicon = response.data.fields.file.errors[0].message);
    }
    uploadSplashImage(files) {
      return this.$upload
        .upload({ url: `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/splash_image`, file: files[0] })
        .then(
          (response) => {
            this.$scope.errors.splash_image = false;
            return this.custom_splash_image = response.data.data.url;
          },
          response => this.$scope.errors.splash_image = response.data.fields.file.errors[0].message);
    }

    copyUrl(file) {
      window.prompt('Copy this:', file.url);
    }

    isDirtyState() {
      if (!angular.equals(this.welcome_box, this.$scope.welcome_box)) { return true; }
      if (!angular.equals(this.values, this.$scope.values)) { return true; }

      return false;
    }

    notifyUrlCopied() {
      this.Growl.success('File URL was copied to your clipboard');
    }

    delete(file) {
      if (window.confirm(`Are you sure you want to remove ${file.name}?`)) {
        return this.$http.delete(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/assets/${file.id}`).success(
          () => this.asset_files = this.asset_files.filter(f => f !== file));
      }
    }

    deleteLogo() {
      return this.$http.delete(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/logo`).success(() => this.custom_logo = null);
    }

    deleteFavicon() {
      return this.$http.delete(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/favicon`).success(() => this.custom_favicon = null);
    }

    deleteSplashImage() {
      return this.$http.delete(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/splash_image`).success(() => this.custom_splash_image = null);
    }

    openUnsplashModal() {
      var event = new CustomEvent('dpLeftDrawer', {detail: {
          module: 'SplashImage',
          width: 0,
          selectImage: this.selectSplashImage.bind(this),
          style: {
            zIndex: 22000
          }
        }});
      window.parent.document.dispatchEvent(event);
    }

    selectSplashImage(image) {
      this.$http.post(`${this.$scope.baseUrl}/portal/api/style/edit-theme-set/unsplash`, image)
        .then(
          (response) => {
            this.$scope.errors.splash_image = false;
            return this.custom_splash_image = response.data.data.url;
          },
          response => this.$scope.errors.splash_image = response.data.fields.file.errors[0].message);
    }

    openAdvancedTab(tab) { return this.advanced_tab = tab; }
    isAdvancedTab(tab) { return this.advanced_tab === tab; }

    isAdvancedExpanded() { return this.is_advanced_expanded; }
    collapseAdvanced() { return this.is_advanced_expanded = false; }
    expandAdvanced() { return this.is_advanced_expanded = true; }

    canPreview() {
      return !this.commiting && !this.recompiling && !this.savingMulti && ((this.preview_as === 'guest') || (this.preview_as === 'myself') || this.preview_as_email);
    }

    previewAs(mode) {
      this.preview_as = mode;
      if ((mode === 'user') || (mode === 'agent')) { this.promptEmail(); }
      this.preview_as_expanded = false;
      this.preview_as_email = null;
      return this.refreshPreviewUrl();
    }

    promptEmail() {
      const { baseUrl } = this.$scope;
      const modalInstance = this.$modal.open({
        templateUrl: this.getTemplatePath('Portal/Editor/email-modal.html'),
        controller:  ['$scope', '$modalInstance', '$http', 'preview_as', function ($scope, $modalInstance, $http, preview_as) {
          $scope.email = '';
          $scope.preview_as = preview_as;
          $scope.ok = function () { return $modalInstance.close(this.email); };
          $scope.cancel = () => $modalInstance.dismiss('cancel');
          $scope.loadEmails = val =>
            $http.get(`${baseUrl}/portal/api/emails?term=${val}&target=${preview_as}`)
                 .then(response => response.data)
          ;
        }
        ],
        resolve: {
          preview_as: () => this.preview_as
        }
      });
      return modalInstance.result.then((email) => { this.preview_as_email = email; return this.refreshPreviewUrl(); });
    }

    error(message) { return this.showAlert(message, 'Changes were not applied'); }
    success(message) { return this.Growl.success(message); }
    serverError(message) {
      if (message && message.message) {
        return this.error(`Server error occurred. Unable to save data (${message.message}).`);
      }
      return this.error('Server error occurred. Unable to save data.');
    }

    cloneTheme() {
      const { baseUrl } = this.$scope;
      const modalInstance = this.$modal.open({
        templateUrl: this.getTemplatePath('Portal/Editor/clone-theme-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.title = '';
          $scope.ok = function () { return $modalInstance.close(this.title); };
          $scope.cancel = () => $modalInstance.dismiss('cancel');
        }
        ]
      });

      modalInstance.result.then((title) => {
        this.$http.post(`${baseUrl}/portal/api/style/edit-theme-set/clone`, { title }).success((data) => {
          this.available_themes.push(data);
          this.Growl.success('Theme is cloned');
        });
      });
    }

    importTheme(files) {
      this.$upload.upload({ url: `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/import`, file: files[0] }).success((data) => {
        this.available_themes.push(data);
        this.Growl.success('Theme is imported');
      });
    }

    importAndReplaceTheme(files) {
      this.$upload.upload({ url: `${this.$scope.baseUrl}/portal/api/style/edit-theme-set/import-and-replace`, file: files[0] }).success((data) => {
        if (data.title) {
          this.selected_theme = data.id;
        } else {
          this.selected_theme = data.theme_id;
        }

        this.editTheme();
        this.Growl.success('Theme is imported');
      });
    }
  }

  AdminPortalCtrlPortalEditor.initClass();

  return AdminPortalCtrlPortalEditor.EXPORT_CTRL();
});
