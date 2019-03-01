define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Labels_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Labels_Ctrl_Edit';
      this.CTRL_AS = 'LabelsEdit';
      this.DEPS = ['em', '$stateParams', '$rootScope', 'LabelDefinition'];
    }

    init() {
      this.type = this.$state.current.data.type;
      this.endpoint = '/labels/definitions';
      if (!this.$stateParams.label) { this.$scope.isNew = true; }
      this.definition = null;
      this.$scope.picker = false;
      this.$scope.colors = [
        '#e11d21', '#eb6420', '#fbca04', '#009800', '#006b75', '#207de5', '#0052cc', '#5319e7',
        '#f7c6c7', '#fad8c7', '#fef2c0', '#bfe5bf', '#bfdadc', '#c7def8', '#bfd4f2', '#d4c5f9'
      ];
      this.$scope.form = { label: '', color: '', label_type: this.type };
      return this.$scope.startDelete = () => this.startDelete();
    }

    state(to) {
      if (to) { to = `.${to}`; }
      return this.$state.current.name.replace(/(.+)\.edit|\.create$/, `$1${to}`);
    }

    initialLoad() {
      if (this.$stateParams.label) {
        return this.LabelDefinition.get(this.type, this.$stateParams.label).then((def) => {
          if (!def) { return; }
          this.definition = def || {};
          return this.$scope.form = angular.copy(def);
        });
      }
    }

    color2hex(color) {
      color = color.replace(/\s/g, '');
      if (/^#?[0-9A-F]{3}$/i.test(color) || /^#?[0-9A-F]{6}$/i.test(color)) {
        if (!color.match(/^#/)) { color = `#${color}`; }
        return color;
      }

      const rgb = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
      if (!rgb) { return '#ffffff'; }
      const hex = x => (`0${parseInt(x).toString(16)}`).slice(-2);

      return `#${hex(rgb[1])}${hex(rgb[2])}${hex(rgb[3])}`;
    }

    saveLabel() {
      let method,
        sendData;
      if (!this.$scope.form.label) { return false; }
      if (this.definition && (this.definition.label === this.$scope.form.label) && (this.definition.color === this.$scope.form.color)) { return false; }

      const dummy = $('<div></div>').css('color', this.color2hex(this.$scope.form.color));
      const color = this.color2hex(dummy.css('color'));
      this.$scope.form.color = color;

      this.startSpinner('saving_label');

      if (this.definition) {
        sendData = { old: this.definition || {}, new: this.$scope.form };
        method = 'sendPutJson';
      } else {
        sendData = this.$scope.form;
        method = 'sendPostJson';
      }

      return this.Api[method](this.endpoint, sendData)

      .success((data) => {
        this.stopSpinner('saving_label', true).then(() => this.Growl.success(this.getRegisteredMessage('saved_label')));

        this.LabelDefinition.update(this.definition, data);
        this.definition = data;

        if (this.$scope.isNew) {
          return this.$state.go(this.state('gocreate'));
        }
        return this.$state.go(this.state('edit'), { label: data.label });
      })

      .error(() => this.Growl.error(this.getRegisteredMessage('not_saved_label'))).finally(() => this.stopSpinner('saving_label', true));
    }


    startDelete() {
      if (!this.definition) { return; }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Labels/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => this.Api.sendDelete(this.endpoint, this.definition)

        .success(() => {
          this.LabelDefinition.remove(this.definition);
          return this.$state.go(this.state(''));
        }).error(() => this.$state.go(this.state(''))).finally(() => this.$state.go(this.state(''))));
    }
  }
  Admin_Labels_Ctrl_Edit.initClass();


  return Admin_Labels_Ctrl_Edit.EXPORT_CTRL();
});
