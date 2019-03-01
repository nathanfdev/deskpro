define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Functions', 'jquery', 'angular'], function(Admin_Ctrl_Base, Functions) {
  class AdminPortalCtrlWidgetEditor extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'AdminPortalCtrlWidgetEditor';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = ['$http', '$location'];
    }

    init() {
      this.$scope.code = '';
      this.$scope.url = {};
      this.$scope.company = {};
      this.$scope.remote_settings = {};
      this.$scope.brand_settings = {};
      this.$scope.global_settings = {};
      this.$scope.jwt_settings = {};
      this.$scope.chat_custom_fields = [];
      this.$scope.user_groups = [];
      this.$scope.user_group_permission = [];
      this.$scope.everyone_group = false;
      this.$scope.reg_group = false;
      this.$scope.enabled_on_portal = false;
      this.$scope.widgetLoaded = false;
      this.$scope.departments = [];
      this.$scope.chat_departments = [];
      this.$scope.languages = [];
      this.$scope.saving_code = false;
      this.$scope.applying_to_portal = false;
      this.$scope.show_embed_help = false;
      this.$scope.pending_changes = false;
      this.$scope.demo_state = 'button';
      this.$scope.formErrors = {};
      this.$scope.flag_has_changed = false;
      this.$scope.section = 'button_settings';
      this.$scope.emailSendInstructions = '';
      this.$scope.brand_id = this.$stateParams.brandId;

      return this.chatFieldsSortOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => this.$scope.$apply(() => {
          let displayOrder = 0;
          return $('.chat-custom-fields').children().each((i, item) => (() => {
            const result = [];
            for (const field of Array.from(this.$scope.brand_settings.chat.custom_fields)) {
              if (field.id === parseInt($(item).data('id'))) {
                field.display_order = displayOrder;
                result.push(displayOrder += 10);
              } else {
                result.push(undefined);
              }
            }
            return result;
          })());
        })
      };
    }

    initialLoad(reset) {
      // setup watchers
      // we should update live demo on form change
      if (reset == null) { reset = false; }
      const updateLiveDemoDebounce = Functions.debounce(() => {
        this.$scope.formErrors = {};
        return this.updateLiveDemo();
      }
      , 350);

      this.$scope.$watch('enabled_on_portal', updateLiveDemoDebounce, true);
      this.$scope.$watch('brand_settings', updateLiveDemoDebounce, true);
      this.$scope.$watch('global_settings', updateLiveDemoDebounce, true);
      this.$scope.$watch('jwt_settings', updateLiveDemoDebounce, true);

      // widget editor bootstrap promises
      // preload form data
      const promises = [];

      // todo better get stored data from local storage
      let savedSettings = localStorage.getItem(`dpWidgetSettings${this.$scope.brand_id}`);

      let promise = this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/widget/setup`);
      promise.then((response) => {
        const { data } = response.data;
        this.$scope.remote_settings = JSON.parse(JSON.stringify(data.settings));
        this.$scope.remote_settings.enabled_on_portal = data.enabled_on_portal;
        this.$scope.remote_settings.jwt = angular.copy(data.jwt_settings);

        this.$scope.url = data.url;
        this.$scope.company = data.company;
        if (!reset) {
          if (savedSettings) {
            savedSettings = JSON.parse(savedSettings);
          }
        } else {
          savedSettings = null;
        }
        if (savedSettings && (savedSettings.global != null) && !jQuery.isEmptyObject(savedSettings.global)) {
          this.$scope.global_settings = savedSettings.global;
        } else {
          this.$scope.global_settings = data.settings.global;
        }
        if (savedSettings && (savedSettings.brand != null) && !jQuery.isEmptyObject(savedSettings.brand)) {
          this.$scope.brand_settings = savedSettings.brand;
        } else {
          this.$scope.brand_settings = data.settings.brand;
        }
        this.$scope.enabled_on_portal = data.enabled_on_portal;
        this.$scope.jwt_settings = data.jwt_settings;

        this.$scope.saving_code = true;
        return this.loadCode().then((codeResponse) => {
          this.$scope.code = codeResponse.data.data;
          return this.$scope.saving_code = false;
        });
      });
      promises.push(promise);

      promise = this.Api2.sendGet('/languages');
      promise.then(res => this.$scope.languages = res.data.data);
      promises.push(promise);

      promise = this.Api2.sendGet('/user_chat_custom_fields?is_enabled=-1');
      promise.then(res => this.$scope.chat_custom_fields = res.data.data);
      promises.push(promise);

      promise = this.Api2.sendGet('/ticket_departments?selectable=1');
      promise.then(res => this.$scope.departments = res.data.data);
      promises.push(promise);

      promise = this.Api2.sendGet('/chat_departments?selectable=1');
      promise.then(res => this.$scope.chat_departments = res.data.data);
      promises.push(promise);

      promise = this.Api2.sendGet('/widget/live_demo/sample_state');
      promise.then(res => this.$scope.sample_state = res.data.data);
      promises.push(promise);

      promise = this.Api2.sendGet('/user_groups');
      promise.then((res) => {
        this.$scope.user_groups = res.data.data;

        // global user group permissions
        for (var group of Array.from(res.data.data)) {
          if (group.sys_name === 'everyone') {
            this.$scope.everyone_group = group;
          }
          if (group.sys_name === 'registered') {
            this.$scope.reg_group = group;
          }

          this.$scope.user_group_permission[group.id] = false;
          for (const permission of Array.from(group.permissions)) {
            if (permission.name === 'chat.use') {
              this.$scope.user_group_permission[group.id] = (permission.value && permission.is_active);
            }
          }
        }

        return (() => {
          const result = [];
          for (group of Array.from(res.data.data)) {
            if (this.$scope.user_group_permission[this.$scope.everyone_group.id]) {
              this.$scope.user_group_permission[group.id] = true;
            }
            if (this.$scope.user_group_permission[this.$scope.reg_group.id] && (group.sys_name !== 'everyone')) {
              result.push(this.$scope.user_group_permission[group.id] = true);
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      });

      promises.push(promise);

      return this.$q.all(promises).then(() => {
        this.initLiveDemo();
        this.updateLiveDemo();

        // order custom fields by brand display order
        for (const field of Array.from(this.$scope.chat_custom_fields)) {
          const brandField = this.getBrandCustomField(field.id);
          if (brandField) {
            field.display_order = brandField.display_order;
          }
        }

        return this.$scope.chat_custom_fields.sort((a, b) => {
          if (a.display_order < b.display_order) {
            return -1;
          }
          if (a.display_order > b.display_order) {
            return 1;
          }
          return 0;
        });
      });
    }

    getFrameNode() {
      return document.getElementById('live-demo');
    }

    getLiveDemoDocument() {
      return this.getFrameNode().contentDocument;
    }

    getOptions(liveDemo) {
      if (liveDemo == null) { liveDemo = false; }
      const options = $.extend(true, { company: this.$scope.company }, this.$scope.brand_settings);
      if ((liveDemo != null) && (options != null ? options.widget : undefined)) {
        options.widget.live_demo = true;
      }

      return options;
    }

    getDpWidget() {
      return this.getFrameNode().contentWindow.DpWidget;
    }

    getWidgetSaveData() {
      return {
        enabled_on_portal: this.$scope.enabled_on_portal,
        settings:          {
          global: this.$scope.global_settings,
          brand:  this.$scope.brand_settings
        },
        jwt_settings: this.$scope.jwt_settings
      };
    }

    getBrandCustomField(fieldId) {
      for (const field of Array.from(this.$scope.brand_settings.chat.custom_fields)) {
        if (field.id === parseInt(fieldId)) {
          return field;
        }
      }
    }

    getLanguage(translation) {
      for (const language of Array.from(this.$scope.languages)) {
        if (translation.language === language.id) {
          return language;
        }
      }
    }

    filterUsedLanguages(translations) {
      return function (language) {
        if (!translations) {
          return true;
        }

        for (const translation of Array.from(translations)) {
          if (language.id === translation.language) {
            return false;
          }
        }

        return true;
      };
    }

    addButtonTranslation(languageId) {
      if (!languageId) {
        return;
      }

      if (!this.$scope.brand_settings.button.translations) {
        this.$scope.brand_settings.button.translations = [];
      }

      return this.$scope.brand_settings.button.translations.push({
        language: parseInt(languageId),
        name:     ''
      });
    }

    addChatPopupTranslation(languageId) {
      if (!languageId) {
        return;
      }

      return this.$scope.brand_settings.chat.popup.translations.push({
        language: parseInt(languageId),
        title:    '',
        message:  ''
      });
    }

    discard() {
      if (confirm('Current edit on the settings will be overridden. Are your sure?')) {
        localStorage.removeItem(`dpWidgetSettings${this.$scope.brand_id}`);
        return this.initialLoad(true).then(() => this.Growl.success('Settings reseted'));
      }
    }

    reset() {
      if (confirm('Current edit on the settings will be reseted. Are your sure?')) {
        localStorage.removeItem(`dpWidgetSettings${this.$scope.brand_id}`);
        return this.Api2.sendDelete(`settings/brands/${this.$scope.brand_id}/widget/setup`).then(() => this.initialLoad(true).then(() => this.Growl.success('Settings reseted')));
      }
    }

    changeFrameSource() {
      if (document.getElementById('iframe-target').value) {
        return this.initLiveDemo();
      }
    }

    changeDemoState(state) {
      this.$scope.demo_state = state;
      if (this.getDpWidget()) {
        return this.getDpWidget().dispatchCustomEvent('changeLiveDemoStage', state);
      }
    }

    changeRights(group, $event) {
      $event.preventDefault();

      const index = this.$scope.brand_settings.chat.user_groups.indexOf(group.id);
      if (index !== -1) {
        return this.$scope.brand_settings.chat.user_groups.splice(index, 1);
      }
      let g,
        i;
      this.$scope.brand_settings.chat.user_groups.push(group.id);

      const everyone = this.$scope.everyone_group.id;
      const reg = this.$scope.reg_group.id;
      if (group.id === everyone) {
        for (i of Object.keys(this.$scope.user_groups || {})) {
          g = this.$scope.user_groups[i];
          if ((g.id !== everyone) && (this.$scope.brand_settings.chat.user_groups.indexOf(g.id) === -1)) {
            this.$scope.brand_settings.chat.user_groups.push(g.id);
          }
        }
        return true;
      } else if (group.id === reg) {
        for (i of Object.keys(this.$scope.user_groups || {})) {
          g = this.$scope.user_groups[i];
          if ((g.id !== everyone) && (g.id !== reg) && (this.$scope.brand_settings.chat.user_groups.indexOf(g.id) === -1)) {
            this.$scope.brand_settings.chat.user_groups.push(g.id);
          }
        }
        return true;
      }
      return true;
    }

    hasChanged() {
      return (angular.toJson(this.$scope.enabled_on_portal) !== angular.toJson(this.$scope.remote_settings.enabled_on_portal)) ||
      (angular.toJson(this.$scope.global_settings) !== angular.toJson(this.$scope.remote_settings.global)) ||
      (angular.toJson(this.$scope.brand_settings) !== angular.toJson(this.$scope.remote_settings.brand)) ||
      (angular.toJson(this.$scope.jwt_settings) !== angular.toJson(this.$scope.remote_settings.jwt));
    }

    loadCode() {
      return this.Api2.sendGet(`settings/brands/${this.$scope.brand_id}/widget/code`);
    }

    loadLiveDemoCode() {
      return this.Api2.sendGet(`settings/brands/${this.$scope.brand_id}/widget/live_demo_code`);
    }

    updateChatCode() {
      this.$scope.code = '';
      this.$scope.saving_code = true;

      const promises = [];
      const promise = this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/widget/setup`, this.getWidgetSaveData(), null, { headers: { 'X-Agent-Request': 'true' } });
      promise.then(
        () => this.loadCode().then((codeResponse) => {
          this.$scope.code = codeResponse.data.data;
          return this.$scope.saving_code = false;
        })
        ,
        (response) => {
          this.$scope.formErrors = __guard__(response.data != null ? response.data.errors : undefined, x => x.fields);
          return this.$scope.saving_code = false;
        });

      promises.push(promise);

      this.startSpinner('saving');
      return this.$q.all(promises).then(() => {
        this.stopSpinner('saving');
        const widgetData = this.getWidgetSaveData();
        this.$scope.remote_settings = {
          global:            angular.copy(widgetData.settings.global),
          brand:             angular.copy(widgetData.settings.brand),
          enabled_on_portal: angular.copy(widgetData.enabled_on_portal),
          jwt:               angular.copy(widgetData.jwt_settings)
        };
        this.$scope.flag_has_changed = this.hasChanged();
        localStorage.removeItem(`dpWidgetSettings${this.$scope.brand_id}`);
        return this.Growl.success('Settings saved');
      }
      , (info) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }

    initLiveDemo() {
      (window.parent || window).addEventListener('message', (event) => {
        if ((event.data != null ? event.data.type : undefined) === 'widgetStatus') {
          return this.$scope.$apply(() => {
            this.$scope.widgetLoaded = true;
            this.getDpWidget().dispatchCustomEvent('setLiveDemoSampleState', {
              sampleState:      this.$scope.sample_state,
              options:          this.getOptions(true),
              settings:         this.$scope.global_settings,
              chatCustomFields: JSON.parse(angular.toJson(this.$scope.chat_custom_fields))
            });
            return this.updateLiveDemo();
          });
        } else if ((event.data != null ? event.data.type : undefined) === 'widgetDemoStage') {
          return this.$scope.$apply(() => this.$scope.demo_state = event.data != null ? event.data.options : undefined);
        }
      }
      , false);

      return this.loadLiveDemoCode().then((codeResponse) => {
        const code = codeResponse.data.data.replace(/widget": {/, 'widget": {\n"live_demo": true,');

        const demoDocument = this.getLiveDemoDocument();
        demoDocument.write(`<body>${code}</body>`);
        demoDocument.close();

        return this.getFrameNode().contentWindow.addEventListener('message', event => parent.window.postMessage(event.data, '*')
        , false);
      });
    }

    updateLiveDemo() {
      this.$scope.flag_has_changed = this.hasChanged();
      try {
        localStorage.setItem(`dpWidgetSettings${this.$scope.brand_id}`, JSON.stringify(this.getWidgetSaveData()));
      } catch (error) {
        console.log('dpWidgetSettings were not saved in local storage');
      }

      if (this.getDpWidget()) {
        this.getDpWidget().dispatchCustomEvent('reloadLiveDemoOptions', this.getOptions(true));
        this.getDpWidget().dispatchCustomEvent('reloadLiveDemoSettings', this.$scope.global_settings);
        return this.getDpWidget().dispatchCustomEvent('setLiveDemoChatCustomFields', JSON.parse(angular.toJson(this.$scope.chat_custom_fields)));
      }
    }

    sendInstructions() {
      return this.Api2
        .sendPostJson(`/settings/brands/${this.$stateParams.brandId}/widget/send-instructions`, { email: this.$scope.emailSendInstructions })
        .success(() => {
          this.Growl.success('Email sent successfully');
          return this.$scope.emailSendInstructions = '';
        }).error(() => this.Growl.error('An error occurred while sending instructions. Try again.'));
    }
  }
  AdminPortalCtrlWidgetEditor.initClass();

  return AdminPortalCtrlWidgetEditor.EXPORT_CTRL();
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
