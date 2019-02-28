define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerIncidents_Ctrl_ServerIncidents extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerIncidents_Ctrl_ServerIncidents';
      this.CTRL_AS   = 'ServerIncidents';
      this.DEPS      = ['Api2'];
    }

    init() {
      return this.incidents = [];
    }

    initialLoad() {
      return this.Api2.sendGet('/system/incidents').then(response => this.incidents = response.data.data);
    }

    getStatus(incident) {
      let status;
      if (incident.resolved) { status = 'Resolved'; } else { status = 'Continuing'; }
      if (incident.dismissed) { status += ' / Dismissed'; }
      return status;
    }

    dismiss(incident) {
      if (confirm(`If you dismiss "${incident.title}" you will no longer receive notifications about this issue. Are you sure?`)) {
        return this.Api2.sendPutJson(`/system/incidents/${incident.id}`, { dismissed: true })
             .success(() => this.incidents.forEach((el) => { if (el.incident.id === incident.id) { return el.incident.dismissed = true; } }))
             .error(() => this.showAlert('Server error. Unable to dismiss the incident.'));
      }
    }

    revertNotifications(incident) {
      return this.Api2.sendPutJson(`/system/incidents/${incident.id}`, { dismissed: false })
           .success(() => this.incidents.forEach((el) => { if (el.incident.id === incident.id) { return el.incident.dismissed = false; } }))
           .error(() => this.showAlert('Server error. Unable to dismiss the incident.'));
    }

    remove(incident) {
      const confirmed = incident.resolved ? true : confirm(`Are you sure you want to remove "${incident.title}"?`);
      if (confirmed) {
        return this.Api2.sendDelete(`/system/incidents/${incident.id}`)
             .success(() => this.incidents = (Array.from(this.incidents).filter(el => el.incident.id !== incident.id)))
             .error(() => this.showAlert('Server error. Unable to dismiss the incident.'));
      }
    }

    removeAll() {
      const confirmed = confirm('Are you sure you want to remove all incidents?');
      if (confirmed) {
        return this.Api2.sendDelete('/system/incidents')
        .success(() => this.incidents = [])
        .error(() => this.showAlert('Server error. Unable to remove incidents.'));
      }
    }

    dismissAll() {
      const confirmed = confirm('Are you sure you want to dismiss notifications for all incidents?');
      if (confirmed) {
        return this.Api2.sendPutJson('/system/incidents', { dismissed: true })
        .success(() => this.incidents.forEach((el) => { console.log(el); return el.incident.dismissed = true; }))
        .error(() => this.showAlert('Server error. Unable to dismiss incidents.'));
      }
    }
  }
  Admin_ServerIncidents_Ctrl_ServerIncidents.initClass();

  return Admin_ServerIncidents_Ctrl_ServerIncidents.EXPORT_CTRL();
});
