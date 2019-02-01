define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Labels_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Labels_Ctrl_List';
      this.DEPS = ['em', '$rootScope', 'LabelDefinition'];
      this.CTRL_AS = 'LabelsList';
    }


    init() {
      this.type = this.$state.current.data.type;
      this.$scope.order = 'label';
      this.$scope.orderReverse = false;
      this.$scope.labels = {};

      this.$scope.countDefinitions = () => {
        let count = 0;
        for (const n of Object.keys(this.$scope.labels || {})) {
          count++;
        }
        return count;
      };

      return this.$scope.$watch('sortOrder', () => {
        if (!this.$scope.sortOrder) { return; }
        this.$scope.order = this.$scope.sortOrder.field;
        return this.$scope.orderReverse = this.$scope.sortOrder.dir === 'DESC';
      });
    }


    type() {
      throw new Exception('This method must be implemented by a sub-class');
    }


    initialLoad() {
      return this.LabelDefinition.all(this.type).then(definitions => this.$scope.labels = definitions);
    }
  }
  Admin_Labels_Ctrl_List.initClass();


  return Admin_Labels_Ctrl_List.EXPORT_CTRL();
});
