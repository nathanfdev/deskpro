define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerIncidents_Ctrl_ServerIncidents extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerIncidents_Ctrl_ServerIncidents'
    @CTRL_AS   = 'ServerIncidents'
    @DEPS      = ['Api2']

    init: ->
      @incidents = []

    initialLoad: ->
      @Api2.sendGet('/system/incidents').then((response) => @incidents = response.data.data)

    getStatus: (incident) ->
      if incident.resolved then status = 'Resolved' else status = 'Continuing'
      if incident.dismissed then status += ' / Dismissed'
      return status

    dismiss: (incident) ->
      if confirm 'If you dismiss "' + incident.title + '" you will no longer receive notifications about this issue. Are you sure?'
        @Api2.sendPutJson('/system/incidents/' + incident.id, {dismissed: true})
             .success => @incidents.forEach (el) -> if el.incident.id == incident.id then el.incident.dismissed = true
             .error => @showAlert('Server error. Unable to dismiss the incident.')

    revertNotifications: (incident) ->
      @Api2.sendPutJson('/system/incidents/' + incident.id, {dismissed: false})
           .success => @incidents.forEach (el) -> if el.incident.id == incident.id then el.incident.dismissed = false
           .error => @showAlert('Server error. Unable to dismiss the incident.')

    remove: (incident) ->
      confirmed = if incident.resolved then true else confirm 'Are you sure you want to remove "' + incident.title + '"?'
      if confirmed
        @Api2.sendDelete('/system/incidents/' + incident.id)
             .success => @incidents = (el for el in @incidents when el.incident.id != incident.id)
             .error => @showAlert('Server error. Unable to dismiss the incident.')

    removeAll: ->
      confirmed = confirm 'Are you sure you want to remove all incidents?'
      if confirmed
        @Api2.sendDelete('/system/incidents')
        .success => @incidents = []
        .error => @showAlert('Server error. Unable to remove incidents.')

    dismissAll: ->
      confirmed = confirm 'Are you sure you want to dismiss notifications for all incidents?'
      if confirmed
        @Api2.sendPutJson('/system/incidents', {dismissed: true})
        .success => @incidents.forEach (el) -> console.log(el); el.incident.dismissed = true;
        .error => @showAlert('Server error. Unable to dismiss incidents.')

  Admin_ServerIncidents_Ctrl_ServerIncidents.EXPORT_CTRL()