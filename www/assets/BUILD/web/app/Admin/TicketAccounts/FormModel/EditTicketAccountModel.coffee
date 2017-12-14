define [
  'DeskPRO/Util/Util'
], (Util) ->
  class Admin_TicketAccounts_FormModel_EditTicketAccountModel
    constructor: (@account, deps, trigger) ->
      @form = {}
      @form.account_type        = @account.account_type || 'tickets';
      @form.address             = @account.address
      @form.is_enabled          = @account.is_enabled
      @form.incoming_type       = 'pop3'
      @form.in_gmail_account    =
        mode: "read"
        read_mailbox_type: "inbox"
        type: 'pop3'
      @form.in_pop3_account     = {}
      @form.in_imap_account     = {}
      @form.in_exchange_account = {}
      @form.in_office365_account  = {}

      @form.outgoing_type     = 'php_mail'
      @form.out_gmail_account =
        type: 'password'
      @form.out_smtp_account  = {}
      @form.out_exchange_account  = {}
      @form.out_office365_account  = {}

      @form.in_pop3_account.secure_mode = "ssl"
      @form.in_imap_account.secure_mode = "ssl"
      @form.in_imap_account.mode = "read"
      @form.in_imap_account.read_mailbox_type = "inbox"
      @form.in_exchange_account.mode = "read"
      @form.in_exchange_account.read_mailbox_type = "inbox"
      @form.out_smtp_account.secure_mode = "ssl"
      @form.in_pop3_account.port = 110;
      @form.out_smtp_account.port = 25;

      if @account.other_addresses and @account.other_addresses.length
        @form.with_email_aliases = true
        @form.other_addresses = @account.other_addresses.join(', ')
      else
        @form.with_email_aliases = false
        @form.other_addresses = ''

      @form.encryption_enabled = (@account.cert_blob? || @account.key_blob?);
      @form.cert_file = @account.cert_blob?.filename
      @form.key_file = @account.key_blob?.filename
      @form.key_pass_phrase = @account.key_pass_phrase

      #--------------------
      # Trigger
      #--------------------

      @form.trigger_actions = {
        SetDepartment: {
          options: {
            department_id: '0'
          }
        },
        SendUserNewEmail: {
          enabled: false,
          options: {}
        }
      }

      if deps and deps.length
        @form.trigger_actions.SetDepartment.options.department_id = deps[0].id+''

      if trigger and trigger.actions?.actions?.length
        for act in trigger.actions.actions
          if act.type == 'SetDepartment'
            @form.trigger_actions.SetDepartment.options = act.options
          else if act.type == 'SendUserNewEmail'
            @form.trigger_actions.SendUserNewEmail.enabled = true
            @form.trigger_actions.SendUserNewEmail.options = act.options

            if ['helpdesk_name', 'site_name', 'performer'].indexOf(@form.trigger_actions.SendUserNewEmail.options.from_name) == -1
              @form.trigger_actions.SendUserNewEmail.options.from_name_custom = @form.trigger_actions.SendUserNewEmail.options.from_name
              @form.trigger_actions.SendUserNewEmail.options.from_name = 'custom'

      if not @form.trigger_actions.SendUserNewEmail.enabled
        @form.trigger_actions.SendUserNewEmail.options = {
          template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
          from_name: 'helpdesk_name'
        }

      #--------------------
      # Init in account
      #--------------------

      if @account.incoming_account_type
        @form.incoming_type = @account.incoming_account_type

        if @form.incoming_type == 'pop3'
          @form.in_pop3_account.host        = @account.incoming_account.host
          @form.in_pop3_account.port        = @account.incoming_account.port
          @form.in_pop3_account.secure_mode = @account.incoming_account.secure_mode
          @form.in_pop3_account.user        = @account.incoming_account.user
          @form.in_pop3_account.password    = @account.incoming_account.password

          @form.in_pop3_account.disable_cert_validation = @account.incoming_account.disable_cert_validation
          if @form.in_pop3_account.secure_mode and @form.in_pop3_account.secure_mode != ''
            @form.in_pop3_account.secure = true

        if @form.incoming_type == 'imap'
          @form.in_imap_account.host        = @account.incoming_account.host
          @form.in_imap_account.port        = @account.incoming_account.port
          @form.in_imap_account.secure_mode = @account.incoming_account.secure_mode
          @form.in_imap_account.no_validation = @account.incoming_account.no_validation
          @form.in_imap_account.user        = @account.incoming_account.user
          @form.in_imap_account.password    = @account.incoming_account.password
          @form.in_imap_account.mode        = @account.incoming_account.mode || 'read'
          if @form.in_imap_account.secure_mode and @form.in_imap_account.secure_mode != ''
            @form.in_imap_account.secure = true
          if @account.incoming_account.read_mailbox and @account.incoming_account.read_mailbox != ''
            @form.in_imap_account.read_mailbox = @account.incoming_account.read_mailbox
            @form.in_imap_account.read_mailbox_type = 'folder'
          if @account.incoming_account.mode == 'archive'
            @form.in_imap_account.archive_mailbox = @account.incoming_account.archive_mailbox

        if @form.incoming_type == 'exchange'
          @form.in_exchange_account.host        = @account.incoming_account.host
          @form.in_exchange_account.user        = @account.incoming_account.user
          @form.in_exchange_account.password    = @account.incoming_account.password
          @form.in_exchange_account.mode        = @account.incoming_account.mode || 'read'
          if @account.incoming_account.read_mailbox and @account.incoming_account.read_mailbox != ''
            @form.in_exchange_account.read_mailbox = @account.incoming_account.read_mailbox
            @form.in_exchange_account.read_mailbox_type = 'folder'
          if @account.incoming_account.mode == 'archive'
            @form.in_exchange_account.archive_mailbox = @account.incoming_account.archive_mailbox

        if @form.incoming_type == 'gmail'
          @form.in_gmail_account.password     = @account.incoming_account.password
          @form.in_gmail_account.token        = @account.incoming_account.token
          @form.in_gmail_account.refreshToken = @account.incoming_account.refreshToken
          @form.in_gmail_account.type         = @account.incoming_account.type || 'pop3'
          @form.in_gmail_account.mode        = @account.incoming_account.mode || 'read'
          if @form.in_gmail_account.secure_mode and @form.in_gmail_account.secure_mode != ''
            @form.in_gmail_account.secure = true
          if @account.incoming_account.read_mailbox and @account.incoming_account.read_mailbox != ''
            @form.in_gmail_account.read_mailbox = @account.incoming_account.read_mailbox
            @form.in_gmail_account.read_mailbox_type = 'folder'
          if @account.incoming_account.mode == 'archive'
            @form.in_gmail_account.archive_mailbox = @account.incoming_account.archive_mailbox

        if @form.incoming_type == 'office365'
          @form.in_office365_account.password = @account.incoming_account.password

      #--------------------
      # Init out account
      #--------------------

      if @account.outgoing_account_type
        @form.outgoing_type = @account.outgoing_account_type

        if @form.outgoing_type == 'smtp'
          @form.out_smtp_account.host        = @account.outgoing_account.host
          @form.out_smtp_account.port        = @account.outgoing_account.port
          @form.out_smtp_account.secure_mode = @account.outgoing_account.secure_mode
          @form.out_smtp_account.user        = @account.outgoing_account.user
          @form.out_smtp_account.password    = @account.outgoing_account.password
          if @form.out_smtp_account.secure_mode and @form.out_smtp_account.secure_mode != ''
            @form.out_smtp_account.disable_cert_validation = @account.outgoing_account.disable_cert_validation
            @form.out_smtp_account.secure = true

        if @form.outgoing_type == 'gmail'
          @form.out_gmail_account.password     = @account.outgoing_account.password
          @form.out_gmail_account.token        = @account.outgoing_account.token
          @form.out_gmail_account.refreshToken = @account.outgoing_account.refreshToken
          @form.out_gmail_account.type         = @account.outgoing_account.type || 'pop3'

        if @form.outgoing_type == 'office365'
          @form.out_office365_account.password = @account.outgoing_account.password

        if @form.outgoing_type == 'exchange'
          @form.out_exchange_account.host        = @account.outgoing_account.host
          @form.out_exchange_account.user        = @account.outgoing_account.user
          @form.out_exchange_account.password    = @account.outgoing_account.password



    getFormData: ->

      form = Util.clone(@form, true)

      if !form.with_email_aliases
        form.other_addresses = ''

      if form.incoming_type == 'gmail'
        form.in_gmail_account.user = form.address
        if form.in_gmail_account.read_mailbox_type == 'inbox' or form.in_gmail_account.read_mailbox == ''
          form.in_gmail_account.read_mailbox = null
        if form.in_gmail_account.mode == 'archive'
          if not @form.in_gmail_account.archive_mailbox or @form.in_gmail_account.archive_mailbox == ''
            form.in_gmail_account.mode = 'read'
            form.in_gmail_account.archive_mailbox = ''
      if form.outgoing_type == 'gmail'
        form.out_gmail_account.user = form.address

      if form.incoming_type == 'office365'
        form.in_office365_account.user = form.address
      if form.outgoing_type == 'office365'
        form.out_office365_account.user = form.address

      if form.incoming_type == 'imap'
        if form.in_imap_account.read_mailbox_type == 'inbox' or form.in_imap_account.read_mailbox == ''
          form.in_imap_account.read_mailbox = null
        if form.in_imap_account.mode == 'archive'
          if not @form.in_imap_account.archive_mailbox or @form.in_imap_account.archive_mailbox == ''
            form.in_imap_account.mode = 'read'
            form.in_imap_account.archive_mailbox = ''

      if form.incoming_type == 'exchange'
        if form.in_exchange_account.read_mailbox_type == 'inbox' or form.in_exchange_account.read_mailbox == ''
          form.in_exchange_account.read_mailbox = null
        if form.in_exchange_account.mode == 'archive'
          if not @form.in_exchange_account.archive_mailbox or @form.in_exchange_account.archive_mailbox == ''
            form.in_exchange_account.mode = 'read'
            form.in_exchange_account.archive_mailbox = ''


      if form.incoming_type == 'pop3'
        if form.in_pop3_account.secure
          form.in_pop3_account.secure_mode = form.in_pop3_account.secure_mode || 'ssl'
        else
          form.in_pop3_account.secure_mode = null
      if form.incoming_type == 'imap'
        if form.in_imap_account.secure
          form.in_imap_account.secure_mode = form.in_imap_account.secure_mode || 'ssl'
          form.in_imap_account.no_validation = form.in_imap_account.no_validation || false
        else
          form.in_imap_account.secure_mode = null
      if form.outgoing_type == 'smtp'
        if form.out_smtp_account.secure
          form.out_smtp_account.secure_mode = form.out_smtp_account.secure_mode || 'ssl'
        else
          form.out_smtp_account.secure_mode = null


      trigger_actions = []
      department_id = parseInt(@form.trigger_actions.SetDepartment?.options?.department_id || 0)
      if department_id
        trigger_actions.push({
          type: 'SetDepartment',
          options: {
            department_id: department_id
          }
        })
      if @form.trigger_actions.SendUserNewEmail?.enabled
        options = @form.trigger_actions.SendUserNewEmail.options

        trigger_actions.push({
          type: 'SendUserNewEmail',
          options: {
            template:  options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            from_name: if options.from_name == 'custom' then (options.from_name_custom || '') else (options.from_name || ''),
            do_cc_users: true,
            from_account: 0
          }
        })

      form.trigger_actions = trigger_actions

      return form

    apply: ->
      @account.address = @form.address
