class DeskproAppStoreConfiguration {
  static get validTargets() {
    return [
      'ticket-sidebar', 'ticket-header-top', 'ticket-header-bottom', 'ticket-header-after',
      'ticket-properties-before', 'ticket-properties-after', 'ticket-replybox-before', 'ticket-replybox-after',
      'ticket-messages-before', 'ticket-messages-after', 'ticket-footer',
      'ticket-properties-new-tab', 'ticket-body-tabs-new-tab',
      'person-sidebar', 'person-header-top', 'person-header-bottom', 'person-header-after',
      'person-content-before', 'person-summary-before', 'person-summary-after', 'person-properties-before',
      'person-properties-after', 'person-notes-before', 'person-notes-after', 'person-content-after',
      'person-contact-before', 'person-contact-after', 'person-org-before', 'person-org-after',
      'person-usergroups-before', 'person-usergroups-after', 'person-footer',
      'person-summary-new-tab', 'person-content-box-new-tab', 'person-notes-new-tab',
      'org-sidebar', 'org-header-top', 'org-header-bottom', 'org-header-after',
      'org-content-before', 'org-summary-before', 'org-summary-after', 'org-notes-before', 'org-notes-after',
      'org-content-after',
      'org-contact-before', 'org-contact-after', 'org-properties-before', 'org-properties-after',
      'org-email-assoc-before', 'org-email-assoc-after', 'org-usergroups-before', 'org-usergroups-after',
      'org-hierarchy-before', 'org-hierarchy-after', 'org-footer',
      'org-summary-new-tab', 'org-content-box-new-tab', 'org-notes-new-tab'
    ];
  }

  static get validEnvironments() {
    return ['development', 'production'];
  }

  static get devAppManifestUrl() {
    return 'http://127.0.0.1:31080/manifest.json';
  }

  constructor(environment, location) {
    this.state = { environment, location };
  }

  get environment() {
    return this.state.environment;
  }

  get endpoint() {
    if (this.environment === 'development') {
      return 'http://127.0.0.1:31080';
    }

    const { location } = this.state;
    return location.origin;
  }
}

export default DeskproAppStoreConfiguration;
