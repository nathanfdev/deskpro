define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_List extends Admin_Ctrl_Base {

    /**
     * Init class.
     */
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_List';
      this.CTRL_AS = 'TicketApprovalsList';
      this.DEPS    = ['$state', '$q', 'DataService'];
    }

    /**
     * Init.
     */
    init() {
      this.dataService = this.DataService.get('TicketApprovals');

      this.types = [];
      this.templates = [];
    }

    /**
     * Initial load.
     */
    initialLoad() {
      return this.$q.all([
        this.dataService.loadApprovalTypes().then(data => this.types = data),
        this.dataService.loadApprovalTemplates().then(data => this.templates = data)
      ]);
    }

    /**
     * Add type to list.
     *
     * @param {Object} type
     * @returns {*}
     */
    addType(type) {
      return this.types.push(type);
    }

    /**
     * Remove type by ID.
     *
     * @param {integer} id
     * @returns {*}
     */
    removeTypeById(id) {
      this.types = this.types.filter(obj => obj.id !== id);

      return this.types;
    }

    /**
     * Update type data by ID.
     *
     * @param {integer} id
     * @param {Object} data
     * @returns {Array}
     */
    updateTypeById(id, data) {
      const index = this.types.findIndex(obj => obj.id === id);
      if (index > -1) {
        this.types[index] = Object.assign(data, { id: id });
      }

      return this.types;
    }

    /**
     * Add type to list.
     *
     * @param {Object} template
     * @returns {*}
     */
    addTemplate(template) {
      return this.templates.push(template);
    }

    /**
     * Remove template by ID.
     *
     * @param {integer} id
     * @returns {*}
     */
    removeTemplateById(id) {
      this.templates = this.templates.filter(obj => obj.id !== id);

      return this.templates;
    }

    /**
     * Update template data by ID.
     *
     * @param {integer} id
     * @param {Object} data
     * @returns {Array}
     */
    updateTemplateById(id, data) {
      const index = this.templates.findIndex(obj => obj.id === id);
      if (index > -1) {
        this.templates[index] = Object.assign(data, { id: id });
      }

      return this.templates;
    }
  }

  Admin_TicketApprovals_Ctrl_List.initClass();

  return Admin_TicketApprovals_Ctrl_List.EXPORT_CTRL();
});
