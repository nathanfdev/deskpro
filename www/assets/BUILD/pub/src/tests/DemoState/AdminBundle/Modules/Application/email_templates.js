export const emailBlocks = {
  list: {
    layout: {
      typeId: 'layout',
      title:  'Layout',
      groups: {
        top: {
          groupId:   'top',
          title:     '',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'layout',
                  groupId:    'top',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_common:email-header.html.twig',
                  type:       'template',
                  showName:   'emails_common/email-header.html',
                  title:      'Header',
                  desc:       'Content that is added to the very top of all emails.'
                },
                {
                  typeId:     'layout',
                  groupId:    'top',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_common:email-footer.html.twig',
                  type:       'template',
                  showName:   'emails_common/email-footer.html',
                  title:      'Footer',
                  desc:       'Content that is added to the very bottom of all emails.'
                },
                {
                  typeId:     'layout',
                  groupId:    'top',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_common:email-custom-css.css.twig',
                  type:       'template',
                  showName:   'emails_common/email-custom-css.css',
                  title:      'Custom CSS',
                  desc:       'Enter any custom CSS rules you want to add to emails.'
                },
                {
                  typeId:     'layout',
                  groupId:    'top',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_common:email-css.css.twig',
                  type:       'template',
                  showName:   'emails_common/email-css.css',
                  title:      'Main CSS',
                  desc:       'This is the default stylesheet applied to emails.'
                }
              ]
            }
          }
        }
      }
    },
    agent: {
      typeId: 'agent',
      title:  'Agent Emails',
      groups: {
        tickets: {
          groupId:   'tickets',
          title:     'Ticket Emails',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'agent',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:ticket-new.html.twig',
                  showName:   'emails_agent/ticket-new.html',
                  title:      'New Ticket Notification',
                  desc:       'Email sent when a new ticket is created.'
                },
                {
                  typeId:     'agent',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:ticket-update.html.twig',
                  showName:   'emails_agent/ticket-update.html',
                  title:      'Ticket Updated Notification',
                  desc:       'Email sent when a ticket has been updated.'
                },
                {
                  typeId:     'agent',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:ticket-reply.html.twig',
                  showName:   'emails_agent/ticket-reply.html',
                  title:      'New Reply',
                  desc:       'Email sent when a new message is added to a ticket.'
                },
                {
                  typeId:     'agent',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_common:ticket-props-table.html.twig',
                  type:       'template',
                  showName:   'emails_common/ticket-props-table.html',
                  title:      'Ticket Properties Table',
                  desc:       'This is the properties table sent to agents in email notifications.'
                },
                {
                  typeId:     'agent',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_common:ticket-fwd-out-header.html.twig',
                  type:       'template',
                  showName:   'emails_common/ticket-fwd-out-header.html',
                  title:      'Forward-out Header',
                  desc:       'This is the content added to the top of a message being forwarded out of the helpdesk.'
                }
              ]
            }
          }
        },
        general: {
          groupId:   'general',
          title:     'General Email',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'agent',
                  groupId:    'general',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:new-agent-chat-message.html.twig',
                  showName:   'emails_agent/new-agent-chat-message.html',
                  title:      'New Chat Message',
                  desc:       'Email sent when someone sends a chat message to an agent and they are offline.'
                },
                {
                  typeId:     'agent',
                  groupId:    'general',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:new-comment.html.twig',
                  showName:   'emails_agent/new-comment.html',
                  title:      'New Comment',
                  desc:       'Email sent when a new comment has been posted to an article, news post or download item.'
                },
                {
                  typeId:     'agent',
                  groupId:    'general',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:new-feedback.html.twig',
                  showName:   'emails_agent/new-feedback.html',
                  title:      'New Feedback',
                  desc:       'Email sent when new feedback has been submitted.'
                },
                {
                  typeId:     'agent',
                  groupId:    'general',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:new-registration.html.twig',
                  showName:   'emails_agent/new-registration.html',
                  title:      'New Registration',
                  desc:       'Email sent when a new user has registered'
                },
                {
                  typeId:     'agent',
                  groupId:    'general',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:agent-welcome.html.twig',
                  showName:   'emails_agent/agent-welcome.html',
                  title:      'Agent Welcome Email',
                  desc:       'The welcome email sent to newly created agents.'
                }
              ]
            }
          }
        },
        tasks: {
          groupId:   'tasks',
          title:     'Tasks',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'agent',
                  groupId:    'tasks',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:task-assigned.html.twig',
                  showName:   'emails_agent/task-assigned.html',
                  title:      'Task Assigned',
                  desc:       'Email sent when a task has been assigned to an agent.'
                },
                {
                  typeId:     'agent',
                  groupId:    'tasks',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:task-completed.html.twig',
                  showName:   'emails_agent/task-completed.html',
                  title:      'Task Completed',
                  desc:       'Email sent when a task has been compelted.'
                },
                {
                  typeId:     'agent',
                  groupId:    'tasks',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:task-due-reminder.html.twig',
                  showName:   'emails_agent/task-due-reminder.html',
                  title:      'Task Reminder',
                  desc:       'Email sent to remind an agent that a task is due.'
                }
              ]
            }
          }
        },
        alerts: {
          groupId:   'alerts',
          title:     'Alerts and Errors',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'agent',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:login-alert.html.twig',
                  showName:   'emails_agent/login-alert.html',
                  title:      'Login Alert',
                  desc:       'Email sent to alert of a login or login attempt.'
                },
                {
                  typeId:     'agent',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:error-invalid-forward.html.twig',
                  showName:   'emails_agent/error-invalid-forward.html',
                  title:      'Error: Invalid Forward',
                  desc:       'Email sent when an agent forwarded an email into the helpdesk, but the helpdesk was unable to parse it.'
                },
                {
                  typeId:     'agent',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:error-marker-missing.html.twig',
                  showName:   'emails_agent/error-marker-missing.html',
                  title:      'Error: Missing Marker',
                  desc:       'Email rejection sent to an agent when their reply was missing the required cut markers.'
                },
                {
                  typeId:     'agent',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:error-unknown-from.html.twig',
                  showName:   'emails_agent/error-unknown-from.html',
                  title:      'Error: Unknown From',
                  desc:       'Email rejection sent to the sender when the account that sent an email does not match what is on record.'
                },
                {
                  typeId:     'agent',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_agent:password-reset-alert.html.twig',
                  showName:   'emails_agent/password-reset-alert.html',
                  title:      'Password Reset Alert',
                  desc:       'Email sent to an agent when an admin has reset their password.'
                }
              ]
            }
          }
        }
      }
    },
    user: {
      typeId: 'user',
      title:  'User Emails',
      groups: {
        tickets: {
          groupId:   'tickets',
          title:     'Ticket Emails',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
                  showName:   'emails_user/ticket-new-autoreply.html',
                  title:      'New Ticket Auto-Response',
                  desc:       'Auto-response email sent to users to confirm the helpdesk received their ticket.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:ticket-new-byagent.html.twig',
                  showName:   'emails_user/ticket-new-byagent.html',
                  title:      'New Ticket by Agent',
                  desc:       'Email sent to a user to notify them that an agent created a new ticket for them.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:new-ticket-guest.html.twig',
                  showName:   'emails_user/new-ticket-guest.html',
                  title:      'New Ticket Confirmation',
                  desc:       'This is an automatic reply sent to a person to confirm the helpdesk received their ticket.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:new-ticket-reg-closed.html.twig',
                  showName:   'emails_user/new-ticket-reg-closed.html',
                  title:      'New Ticket Denied (Registration Closed)',
                  desc:       'Email rejection sent to a user to tell them their ticket was not accepted because they do not have an account.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
                  showName:   'emails_user/ticket-reply-byagent.html',
                  title:      'New Agent Reply',
                  desc:       'Email reply sent to users.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
                  showName:   'emails_user/ticket-reply-autoreply.html',
                  title:      'User Reply Auto-Response',
                  desc:       'Email auto-response sent to users to confirm the helpdesk received their reply.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:new-reply-reject-resolved.html.twig',
                  showName:   'emails_user/new-reply-reject-resolved.html',
                  title:      'New reply rejected because ticket is resolved',
                  desc:       'Email rejection sent to a user to tell them their reply was not accepted because their ticket is closed.'
                }
              ]
            },
            validation: {
              subGroupId: 'validation',
              title:      'Validation',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'validation',
                  name:       'DeskPRO:emails_user:new-ticket-validate.html.twig',
                  showName:   'emails_user/new-ticket-validate.html',
                  title:      'New Ticket (Validation required)',
                  desc:       'Email sent to a user after submitting their first ticket with instructions on how to validate their account.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'validation',
                  name:       'DeskPRO:emails_user:ticket-new-validate-email.html.twig',
                  showName:   'emails_user/ticket-new-validate-email.html',
                  title:      'Ticket New Validate Email',
                  desc:       'Email sent to a user after they have registered to tell them they must validate their email address.'
                }
              ]
            },
            warnings: {
              subGroupId: 'warnings',
              title:      'Warnings, alerts & errors',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'warnings',
                  name:       'DeskPRO:emails_user:ticket-awaiting-warn.html.twig',
                  showName:   'emails_user/ticket-awaiting-warn.html',
                  title:      'Ticket Awaiting Warning',
                  desc:       'Email sent to a user to ask them to answer a ticket'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'warnings',
                  name:       'DeskPRO:emails_user:ticket-awaiting-warn-final.html.twig',
                  showName:   'emails_user/ticket-awaiting-warn-final.html',
                  title:      'Ticket Awaiting Final Warning',
                  desc:       'Email sent to a user to notify them their ticket is about to be automatically closed'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'warnings',
                  name:       'DeskPRO:emails_user:ticket-autoclose-warn.html.twig',
                  showName:   'emails_user/ticket-autoclose-warn.html',
                  title:      'Auto-Close Warning',
                  desc:       'Email sent to a user to warn them that their ticket will be auto-closed soon.'
                }
              ]
            },
            rating: {
              subGroupId: 'rating',
              title:      'Rating',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'rating',
                  name:       'DeskPRO:emails_user:ticket-rate.html.twig',
                  showName:   'emails_user/ticket-rate.html',
                  title:      'Ticket Rating Request',
                  desc:       'Email sent to a user to ask them to rate their ticket support.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'rating',
                  name:       'DeskPRO:emails_common:ticket-rating-links.html.twig',
                  type:       'template',
                  showName:   'emails_common/ticket-rating-links.html',
                  title:      'Inline Ticket Rating Links',
                  desc:       'These are the links that appear under agent replies asking the user to rate their support.'
                }
              ]
            },
            participants: {
              subGroupId: 'participants',
              title:      'CC and new participants',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'participants',
                  name:       'DeskPRO:emails_user:ticket-add-cc.html.twig',
                  type:       'template',
                  showName:   'emails_user/ticket-add-cc.html',
                  title:      'Ticket CC Notice',
                  desc:       'Email sent to a person inform them that they have been added as a participant on a ticket.'
                },
                {
                  typeId:     'user',
                  groupId:    'tickets',
                  subGroupId: 'participants',
                  name:       'DeskPRO:emails_user:ticket-participant.html.twig',
                  type:       'template',
                  showName:   'emails_user/ticket-participant.html',
                  title:      'Ticket New Participant',
                  desc:       'Email sent to a user inform them that they have been added as a participant on a ticket.'
                }
              ]
            }
          }
        },
        account: {
          groupId:   'account',
          title:     'Account Emails',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:register-welcome.html.twig',
                  showName:   'emails_user/register-welcome.html',
                  title:      'Register Welcome Email',
                  desc:       'Email sent to a user after they have registered.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:register-welcome-byagent.html.twig',
                  showName:   'emails_user/register-welcome-byagent.html',
                  title:      'Register By Agent Welcome Email',
                  desc:       'Email sent to a user after an agent has created an account for them.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:email-validation.html.twig',
                  showName:   'emails_user/email-validation.html',
                  title:      'Email Validation',
                  desc:       'Email sent to a user to ask them to validate their email address'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:new-email-validate-primary.html.twig',
                  showName:   'emails_user/new-email-validate-primary.html',
                  title:      'Validate Primary Email',
                  desc:       'Email sent to a user to validate their primary email address.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:new-email-validate.html.twig',
                  showName:   'emails_user/new-email-validate.html',
                  title:      'Validate Additional Email',
                  desc:       'Email sent to a user when they add an additional email address on their account and need to validate it.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:reset-password.html.twig',
                  showName:   'emails_user/reset-password.html',
                  title:      'Reset Password',
                  desc:       'Email sent to a user when they request a password reset.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:set-password.html.twig',
                  showName:   'emails_user/set-password.html',
                  title:      'Set Password',
                  desc:       'Email sent to a user to ask them to set a password.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:agent-changed-password.html.twig',
                  showName:   'emails_user/agent-changed-password.html',
                  title:      'Agent Reset Password',
                  desc:       'Email sent to a user when an agent has reset their account password.'
                },
                {
                  typeId:     'user',
                  groupId:    'account',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:account-disabled.html.twig',
                  showName:   'emails_user/account-disabled.html',
                  title:      'Account Disabled',
                  desc:       'Email sent to a user when their account has been disabled.'
                }
              ]
            }
          }
        },
        chat: {
          groupId:   'chat',
          title:     'Chat Emails',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'chat',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:chat-transcript.html.twig',
                  showName:   'emails_user/chat-transcript.html',
                  title:      'Chat Transcript',
                  desc:       'Email sent to a user after they have finished a chat.'
                }
              ]
            }
          }
        },
        publish: {
          groupId:   'publish',
          title:     'Publish Emails',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:comment-approved.html.twig',
                  showName:   'emails_user/comment-approved.html',
                  title:      'Comment Approved',
                  desc:       'Email sent to a user when one of their comments were approved.'
                },
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:comment-deleted.html.twig',
                  showName:   'emails_user/comment-deleted.html',
                  title:      'Comment Deleted',
                  desc:       'Email sent to a user when an agent deletes one of their comments.'
                },
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:comment-new.html.twig',
                  showName:   'emails_user/comment-new.html',
                  title:      'Comment Thank-you',
                  desc:       'Email sent to a user to a user after they submit a comment.'
                },
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:download-subscription.html.twig',
                  showName:   'emails_user/download-subscription.html',
                  title:      'New Download',
                  desc:       'Email sent to a user to notify them of new downloads in a subscribed category'
                },
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:feedback-subscription.html.twig',
                  showName:   'emails_user/feedback-subscription.html',
                  title:      'New Feedback',
                  desc:       'Email sent the user to notify them of new feedbacks in a subscribed category.'
                },
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:kb-subscription.html.twig',
                  showName:   'emails_user/kb-subscription.html',
                  title:      'Knowledgebase Subscription',
                  desc:       'Email sent to a user when changes were made to their subscribed categories or articles.'
                },
                {
                  typeId:     'user',
                  groupId:    'publish',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:news-subscription.html.twig',
                  showName:   'emails_user/news-subscription.html',
                  title:      'New News Post',
                  desc:       'Email sent the user to notify them of new news posts in a subscribed category.'
                }
              ]
            }
          }
        },
        feedback: {
          groupId:   'feedback',
          title:     'Feedback Emails',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'feedback',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:feedback-approved.html.twig',
                  showName:   'emails_user/feedback-approved.html',
                  title:      'Feedback Approved',
                  desc:       'Email sent to a user when their feedback has been approved.'
                },
                {
                  typeId:     'user',
                  groupId:    'feedback',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:feedback-disapproved.html.twig',
                  showName:   'emails_user/feedback-disapproved.html',
                  title:      'Feedback Deleted',
                  desc:       'Email sent to a user when their feedback was not approved.'
                },
                {
                  typeId:     'user',
                  groupId:    'feedback',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:feedback-new.html.twig',
                  showName:   'emails_user/feedback-new.html',
                  title:      'Feedback Thank-you',
                  desc:       'Email sent to a user after they submit new feedback.'
                },
                {
                  typeId:     'user',
                  groupId:    'feedback',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:feedback-new-comment.html.twig',
                  showName:   'emails_user/feedback-new-comment.html',
                  title:      'Feedback Comment Thank-you',
                  desc:       'Email sent to a user after they submit a new comment on feedback.'
                },
                {
                  typeId:     'user',
                  groupId:    'feedback',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:feedback-updated.html.twig',
                  showName:   'emails_user/feedback-updated.html',
                  title:      'Feedback Updated Notification',
                  desc:       'Email sent to a user when their feedback post has been updated.'
                }
              ]
            }
          }
        },
        alerts: {
          groupId:   'alerts',
          title:     'Alerts and Errors',
          subGroups: {
            primary: {
              subGroupId: 'primary',
              title:      'Primary',
              templates:  [
                {
                  typeId:     'user',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:login-alert.html.twig',
                  showName:   'emails_user/login-alert.html',
                  title:      'Login Alert',
                  desc:       'Email sent to users to notify them of logins or attempted logins using their account.'
                },
                {
                  typeId:     'user',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:gateway-autoresponse-warn.html.twig',
                  showName:   'emails_user/gateway-autoresponse-warn.html',
                  title:      'Auto-Responder Warning',
                  desc:       'Email sent to a user when the helpdesk detects that they may be an auto-responder.'
                },
                {
                  typeId:     'user',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:email-too-big.html.twig',
                  showName:   'emails_user/email-too-big.html',
                  title:      'Email Too Big',
                  desc:       'Email sent to a user when they sent an email that is larger than is allowed.'
                },
                {
                  typeId:     'user',
                  groupId:    'alerts',
                  subGroupId: 'primary',
                  name:       'DeskPRO:emails_user:rate-limit-notice.html.twig',
                  showName:   'emails_user/rate-limit-notice.html',
                  title:      'Rate Limit Notice',
                  desc:       'Email sent the user to inform them that the email ticket limit has been reached.'
                }
              ]
            }
          }
        }
      }
    },
    custom: {
      title:  'Custom Emails',
      typeId: 'custom',
      groups: {
        custom: {
          groupId:   'custom',
          title:     'Custom Emails',
          templates: []
        }
      }
    }
  }
};
export const variables = {
  recipient: {
    description: 'Email recipient.',
    type:        'object (Person)',
    attribute:   'recipient'
  },
  ticket: {
    description: 'The ticket.',
    type:        'object (Ticket)',
    attribute:   'ticket',
    properties:  {
      id: {
        description: '',
        type:        'integer',
        attribute:   'id'
      },
      ref: {
        description: '',
        type:        'string',
        attribute:   'ref'
      },
      auth: {
        description: '',
        type:        'string',
        attribute:   'auth'
      },
      parent_ticket: {
        description: 'Parent ticket.',
        type:        'object (Ticket)',
        attribute:   'parent_ticket'
      },
      children_tickets: {
        description: '',
        type:        'array of objects (Ticket)',
        attribute:   'children_tickets'
      },
      language: {
        description: 'The language the ticket is in.',
        type:        'object (Language)',
        attribute:   'language',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          sys_name: {
            description: 'The unique sys name assigned to the language.',
            type:        'string',
            attribute:   'sys_name'
          },
          lang_code: {
            description: 'The three-letter ISO 639-2 code.',
            type:        'string',
            attribute:   'lang_code'
          },
          title: {
            description: 'Title of the language.',
            type:        'string',
            attribute:   'title'
          },
          locale: {
            description: 'The locale code.',
            type:        'string',
            attribute:   'locale'
          },
          flag_image: {
            description: 'String path to image.',
            type:        'string',
            attribute:   'flag_image'
          },
          has_user: {
            description: 'True if has user.',
            type:        'boolean',
            attribute:   'has_user'
          },
          has_agent: {
            description: 'True if has agent.',
            type:        'boolean',
            attribute:   'has_agent'
          },
          has_admin: {
            description: 'True if has admin.',
            type:        'boolean',
            attribute:   'has_admin'
          }
        }
      },
      brand: {
        description: '',
        type:        'object (Brand)',
        attribute:   'brand',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          name: {
            description: 'The brand name.',
            type:        'string',
            attribute:   'name'
          },
          url: {
            description: 'The brand url.',
            type:        'string',
            attribute:   'url'
          }
        }
      },
      department: {
        description: '',
        type:        'object (Department)',
        attribute:   'department',
        properties:  {
          id: {
            description: '',
            type:        'integer',
            attribute:   'id'
          },
          parent: {
            description: '',
            type:        'object (Department)',
            attribute:   'parent'
          },
          children: {
            description: '',
            type:        'array of objects (Department)',
            attribute:   'children'
          },
          title: {
            description: '',
            type:        'string',
            attribute:   'title'
          },
          user_title: {
            description: '',
            type:        'string',
            attribute:   'user_title'
          },
          is_tickets_enabled: {
            description: '',
            type:        'boolean',
            attribute:   'is_tickets_enabled'
          },
          is_chat_enabled: {
            description: '',
            type:        'boolean',
            attribute:   'is_chat_enabled'
          },
          display_order: {
            description: '',
            type:        'integer',
            attribute:   'display_order'
          },
          avatar: {
            description: '',
            type:        'object (Blob)',
            attribute:   'avatar',
            properties:  {
              id: {
                description: '',
                type:        'integer',
                attribute:   'id'
              },
              sys_name: {
                description: 'A unique system name for the blob.',
                type:        'string',
                attribute:   'sys_name'
              },
              original_blob: {
                description: 'Sometimes we might have multiple versions of a file. For example, if a file has been\ncropped then the cropped file is saved as its own blob, but the original\nis linked here.',
                type:        'object (Blob)',
                attribute:   'original_blob'
              },
              storage_loc: {
                description: 'The storage adapter that knows how to load this file.',
                type:        'string',
                attribute:   'storage_loc'
              },
              storage_loc_pref: {
                description: "The preferred storage adapter. This is used to mark when we want to move\na file from one storage location to another. For example, if an upload\nto S3 failed and we saved the file in the database instead,\nthen the $storage_loc would be 'db' but $storage_loc_pref would be 's3'.\n\nThe cron jobs will look for when these two values don't match and will\nattempt to move resources gradually.",
                type:        'string',
                attribute:   'storage_loc_pref'
              },
              storage_loc_specific: {
                description: "A set storage adapter. This is used to 'set' a storage adapter that will\nbe used even if the system would normally use a different one.\n\nThe main usage for this is to store things like logfiles in the database\ninstead of something like s3. DeskPRO saves lots of logfiles, so its\npretty inefficient to send off lots of small logfiles to s3.",
                type:        'string',
                attribute:   'storage_loc_specific'
              },
              save_path: {
                description: "The path to the file if it's not stored in the database.",
                type:        'string',
                attribute:   'save_path'
              },
              file_url: {
                description: 'The HTTP link to download the file.',
                type:        'string',
                attribute:   'file_url'
              },
              filename: {
                description: 'The original filename.',
                type:        'string',
                attribute:   'filename'
              },
              filesize: {
                description: 'The file size.',
                type:        'integer',
                attribute:   'filesize'
              },
              content_type: {
                description: 'The files mimetype.',
                type:        'string',
                attribute:   'content_type'
              },
              authcode: {
                description: '',
                type:        'string',
                attribute:   'authcode'
              },
              blob_hash: {
                description: '',
                type:        'string',
                attribute:   'blob_hash'
              },
              is_media_upload: {
                description: 'Is this a media upload (appears in the media browser etc). These are files that were\nuploaded and are attached to things.',
                type:        'boolean',
                attribute:   'is_media_upload'
              },
              title: {
                description: 'The title of this file used in interfaces if its a media upload.',
                type:        'string',
                attribute:   'title'
              },
              dim_w: {
                description: 'If this type of file has dimentions, the width.',
                type:        'integer',
                attribute:   'dim_w'
              },
              dim_h: {
                description: 'If this type of file has dimentions, the height.',
                type:        'integer',
                attribute:   'dim_h'
              },
              date_created: {
                description: '',
                type:        'DateTime',
                attribute:   'date_created'
              },
              is_temp: {
                description: '',
                type:        'boolean',
                attribute:   'is_temp'
              },
              labels: {
                description: '',
                type:        'array of objects (LabelBlob)',
                attribute:   'labels',
                properties:  {
                  blob: {
                    description: '',
                    type:        'object (Blob)',
                    attribute:   'blob'
                  }
                }
              }
            }
          },
          brands: {
            description: '',
            type:        'array of objects (Brand)',
            attribute:   'brands'
          },
          project_members: {
            description: '',
            type:        'array of objects (ProjectMember)',
            attribute:   'project_members',
            properties:  {
              id: {
                description: 'The unique ID.',
                type:        'integer',
                attribute:   'id'
              },
              project: {
                description: 'Project entity.',
                type:        'integer id (Project)',
                attribute:   'project'
              },
              person: {
                description: 'A person attached to a project.',
                type:        'integer id (Person)',
                attribute:   'person'
              },
              team: {
                description: 'An agent team attached to project.',
                type:        'integer id (AgentTeam)',
                attribute:   'team'
              },
              department: {
                description: 'A department attached to project.',
                type:        'integer id (AgentTeam)',
                attribute:   'department'
              }
            }
          },
          permissions: {
            description: '',
            type:        'array of objects (DepartmentPermission)',
            attribute:   'permissions',
            properties:  {
              id: {
                description: '',
                type:        'integer',
                attribute:   'id'
              },
              department: {
                description: '',
                type:        'object (Department)',
                attribute:   'department'
              },
              usergroup: {
                description: 'The connected usergroup. If this is set, then person cannot be set.',
                type:        'object (Usergroup)',
                attribute:   'usergroup',
                properties:  {
                  id: {
                    description: 'The unique ID, DB-generated.',
                    type:        'integer',
                    attribute:   'id'
                  },
                  title: {
                    description: 'Title of the usergroup.',
                    type:        'string',
                    attribute:   'title'
                  },
                  note: {
                    description: 'A note or description about the usergroup.',
                    type:        'string',
                    attribute:   'note'
                  },
                  is_agent_group: {
                    description: 'Is this an agent group?',
                    type:        'boolean',
                    attribute:   'is_agent_group'
                  },
                  sys_name: {
                    description: 'When non-null, the group is a special system group (hidden from most interfaces).',
                    type:        'string',
                    attribute:   'sys_name'
                  },
                  is_enabled: {
                    description: 'Is the group enabled?',
                    type:        'boolean',
                    attribute:   'is_enabled'
                  },
                  permissions: {
                    description: 'Usergroup permissions.',
                    type:        'object (Permission)',
                    attribute:   'permissions',
                    properties:  {
                      id: {
                        description: 'The unique ID.',
                        type:        'integer',
                        attribute:   'id'
                      },
                      name: {
                        description: 'The name of the permission.',
                        type:        'string',
                        attribute:   'name'
                      },
                      value: {
                        description: 'Any numeric number (ex filesize, flag).',
                        type:        'integer',
                        attribute:   'value'
                      },
                      is_active: {
                        description: "True means this permission record is active for normal use with the permission resolver.\n\nWhen a $person permission is used but the $person in question is also part\nof a usergroup, then this record might be superfluous: If the ug grants the perm,\nand this record grants the perm, then we have two records that both grant the perm.\n\nThis isn't harmful usually but if you have many many agents defined and they all have\nthese duplicative perms, then you end up with many thousands of extra rows, which are all\nfetched and processed with the permission resolver.\n\nSo we turn these extra perms \"off\" so the resolver doesn't fetch them. That means if a\nhd with many agent uses groups instead of overrides, permission resolving is much much faster.",
                        type:        'boolean',
                        attribute:   'is_active'
                      }
                    }
                  }
                }
              },
              person: {
                description: 'The connected person. If this is set, then usergroup cannot be set.',
                type:        'object (Person)',
                attribute:   'person'
              },
              app: {
                description: '',
                type:        'string',
                attribute:   'app'
              },
              name: {
                description: 'The name of the permission.',
                type:        'string',
                attribute:   'name'
              },
              value: {
                description: 'Any numeric number (ex filesize, flag).',
                type:        'string',
                attribute:   'value'
              },
              is_active: {
                description: '',
                type:        'boolean',
                attribute:   'is_active'
              }
            }
          }
        }
      },
      category: {
        description: '',
        type:        'object (TicketCategory)',
        attribute:   'category',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          parent: {
            description: 'This category parent.',
            type:        'integer id (TicketCategory)',
            attribute:   'parent'
          },
          title: {
            description: 'Category title.',
            type:        'string',
            attribute:   'title'
          },
          display_order: {
            description: "Obviously it's display order for lists.",
            type:        'integer',
            attribute:   'display_order'
          }
        }
      },
      priority: {
        description: '',
        type:        'object (TicketPriority)',
        attribute:   'priority',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          title: {
            description: 'Priority title.',
            type:        'string',
            attribute:   'title'
          },
          priority: {
            description: 'The priority itself.',
            type:        'integer',
            attribute:   'priority'
          }
        }
      },
      workflow: {
        description: '',
        type:        'object (TicketWorkflow)',
        attribute:   'workflow',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          title: {
            description: 'Workflow title.',
            type:        'string',
            attribute:   'title'
          },
          display_order: {
            description: 'Workflow display order.',
            type:        'integer',
            attribute:   'display_order'
          }
        }
      },
      product: {
        description: '',
        type:        'object (Product)',
        attribute:   'product',
        properties:  {
          id: {
            description: 'The unique id of the category.',
            type:        'integer',
            attribute:   'id'
          },
          title: {
            description: 'Category`s title.',
            type:        'string',
            attribute:   'title'
          },
          display_order: {
            description: 'Display order.',
            type:        'integer',
            attribute:   'display_order'
          },
          parent: {
            description: 'Parent of the product.',
            type:        'integer id (Product)',
            attribute:   'parent'
          }
        }
      },
      person: {
        description: '',
        type:        'object (Person)',
        attribute:   'person'
      },
      person_email: {
        description: '',
        type:        'object (PersonEmail)',
        attribute:   'person_email',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          person: {
            description: 'Person owner of email.',
            type:        'integer id (Person)',
            attribute:   'person'
          },
          email: {
            description: 'The email address.',
            type:        'string',
            attribute:   'email'
          },
          email_domain: {
            description: 'The email address domain.',
            type:        'string',
            attribute:   'email_domain'
          },
          is_validated: {
            description: 'True if validation passed.',
            type:        'boolean',
            attribute:   'is_validated'
          },
          date_created: {
            description: 'The original time the email was created.',
            type:        'DateTime',
            attribute:   'date_created'
          }
        }
      },
      agent: {
        description: '',
        type:        'object (Person)',
        attribute:   'agent'
      },
      agent_team: {
        description: '',
        type:        'object (AgentTeam)',
        attribute:   'agent_team',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          name: {
            description: 'The name of agent team.',
            type:        'string',
            attribute:   'name'
          }
        }
      },
      organization: {
        description: '',
        type:        'object (Organization)',
        attribute:   'organization',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          picture_blob: {
            description: 'The org picture.',
            type:        'object (Blob)',
            attribute:   'picture_blob',
            properties:  {
              id: {
                description: '',
                type:        'integer',
                attribute:   'id'
              },
              sys_name: {
                description: 'A unique system name for the blob.',
                type:        'string',
                attribute:   'sys_name'
              },
              original_blob: {
                description: 'Sometimes we might have multiple versions of a file. For example, if a file has been\ncropped then the cropped file is saved as its own blob, but the original\nis linked here.',
                type:        'object (Blob)',
                attribute:   'original_blob'
              },
              storage_loc: {
                description: 'The storage adapter that knows how to load this file.',
                type:        'string',
                attribute:   'storage_loc'
              },
              storage_loc_pref: {
                description: "The preferred storage adapter. This is used to mark when we want to move\na file from one storage location to another. For example, if an upload\nto S3 failed and we saved the file in the database instead,\nthen the $storage_loc would be 'db' but $storage_loc_pref would be 's3'.\n\nThe cron jobs will look for when these two values don't match and will\nattempt to move resources gradually.",
                type:        'string',
                attribute:   'storage_loc_pref'
              },
              storage_loc_specific: {
                description: "A set storage adapter. This is used to 'set' a storage adapter that will\nbe used even if the system would normally use a different one.\n\nThe main usage for this is to store things like logfiles in the database\ninstead of something like s3. DeskPRO saves lots of logfiles, so its\npretty inefficient to send off lots of small logfiles to s3.",
                type:        'string',
                attribute:   'storage_loc_specific'
              },
              save_path: {
                description: "The path to the file if it's not stored in the database.",
                type:        'string',
                attribute:   'save_path'
              },
              file_url: {
                description: 'The HTTP link to download the file.',
                type:        'string',
                attribute:   'file_url'
              },
              filename: {
                description: 'The original filename.',
                type:        'string',
                attribute:   'filename'
              },
              filesize: {
                description: 'The file size.',
                type:        'integer',
                attribute:   'filesize'
              },
              content_type: {
                description: 'The files mimetype.',
                type:        'string',
                attribute:   'content_type'
              },
              authcode: {
                description: '',
                type:        'string',
                attribute:   'authcode'
              },
              blob_hash: {
                description: '',
                type:        'string',
                attribute:   'blob_hash'
              },
              is_media_upload: {
                description: 'Is this a media upload (appears in the media browser etc). These are files that were\nuploaded and are attached to things.',
                type:        'boolean',
                attribute:   'is_media_upload'
              },
              title: {
                description: 'The title of this file used in interfaces if its a media upload.',
                type:        'string',
                attribute:   'title'
              },
              dim_w: {
                description: 'If this type of file has dimentions, the width.',
                type:        'integer',
                attribute:   'dim_w'
              },
              dim_h: {
                description: 'If this type of file has dimentions, the height.',
                type:        'integer',
                attribute:   'dim_h'
              },
              date_created: {
                description: '',
                type:        'DateTime',
                attribute:   'date_created'
              },
              is_temp: {
                description: '',
                type:        'boolean',
                attribute:   'is_temp'
              },
              labels: {
                description: '',
                type:        'array of objects (LabelBlob)',
                attribute:   'labels',
                properties:  {
                  blob: {
                    description: '',
                    type:        'object (Blob)',
                    attribute:   'blob'
                  }
                }
              }
            }
          },
          name: {
            description: 'The organization name.',
            type:        'string',
            attribute:   'name'
          },
          summary: {
            description: 'The summary field as filled in by agents.',
            type:        'string',
            attribute:   'summary'
          },
          importance: {
            description: 'The org importance.',
            type:        'integer',
            attribute:   'importance'
          },
          custom_data: {
            description: '',
            type:        'array of objects (CustomDataOrganization)',
            attribute:   'custom_data',
            properties:  {
              organization: {
                description: '',
                type:        'object (Organization)',
                attribute:   'organization'
              },
              field: {
                description: '',
                type:        'object (CustomDefOrganization)',
                attribute:   'field',
                properties:  {
                  choices: {
                    description: '',
                    type:        'array',
                    attribute:   'choices'
                  },
                  widget_type: {
                    description: 'Gets the widget type. This is the same as the type, except if this is a choice\nwe return the real type of field (e.g., checkbox or radio) based on display options.',
                    type:        'string',
                    attribute:   'widget_type'
                  },
                  id: {
                    description: 'The unique ID.',
                    type:        'integer',
                    attribute:   'id'
                  },
                  title: {
                    description: 'The title.',
                    type:        'string',
                    attribute:   'title'
                  },
                  description: {
                    description: 'The description.',
                    type:        'string',
                    attribute:   'description'
                  },
                  options: {
                    description: 'Options for the field.',
                    type:        'array',
                    attribute:   'options'
                  },
                  is_user_enabled: {
                    description: 'Can the field be viewed by the user?',
                    type:        'boolean',
                    attribute:   'is_user_enabled'
                  },
                  is_enabled: {
                    description: 'True if field is enabled.',
                    type:        'boolean',
                    attribute:   'is_enabled'
                  },
                  display_order: {
                    description: 'Obviously it is field`s display order.',
                    type:        'integer',
                    attribute:   'display_order'
                  },
                  is_agent_field: {
                    description: 'Is this field associated with agents only.',
                    type:        'boolean',
                    attribute:   'is_agent_field'
                  },
                  parent: {
                    description: 'Field`s parent.',
                    type:        'integer id (CustomDefOrganization)',
                    attribute:   'parent'
                  }
                }
              },
              root_field: {
                description: '',
                type:        'object (CustomDefOrganization)',
                attribute:   'root_field'
              }
            }
          },
          usergroups: {
            description: 'Usergroups the user belongs to.',
            type:        'array of objects (Usergroup)',
            attribute:   'usergroups',
            properties:  {
              id: {
                description: 'The unique ID, DB-generated.',
                type:        'integer',
                attribute:   'id'
              },
              title: {
                description: 'Title of the usergroup.',
                type:        'string',
                attribute:   'title'
              },
              note: {
                description: 'A note or description about the usergroup.',
                type:        'string',
                attribute:   'note'
              },
              is_agent_group: {
                description: 'Is this an agent group?',
                type:        'boolean',
                attribute:   'is_agent_group'
              },
              sys_name: {
                description: 'When non-null, the group is a special system group (hidden from most interfaces).',
                type:        'string',
                attribute:   'sys_name'
              },
              is_enabled: {
                description: 'Is the group enabled?',
                type:        'boolean',
                attribute:   'is_enabled'
              },
              permissions: {
                description: 'Usergroup permissions.',
                type:        'object (Permission)',
                attribute:   'permissions',
                properties:  {
                  id: {
                    description: 'The unique ID.',
                    type:        'integer',
                    attribute:   'id'
                  },
                  name: {
                    description: 'The name of the permission.',
                    type:        'string',
                    attribute:   'name'
                  },
                  value: {
                    description: 'Any numeric number (ex filesize, flag).',
                    type:        'integer',
                    attribute:   'value'
                  },
                  is_active: {
                    description: "True means this permission record is active for normal use with the permission resolver.\n\nWhen a $person permission is used but the $person in question is also part\nof a usergroup, then this record might be superfluous: If the ug grants the perm,\nand this record grants the perm, then we have two records that both grant the perm.\n\nThis isn't harmful usually but if you have many many agents defined and they all have\nthese duplicative perms, then you end up with many thousands of extra rows, which are all\nfetched and processed with the permission resolver.\n\nSo we turn these extra perms \"off\" so the resolver doesn't fetch them. That means if a\nhd with many agent uses groups instead of overrides, permission resolving is much much faster.",
                    type:        'boolean',
                    attribute:   'is_active'
                  }
                }
              }
            }
          },
          auto_cc_people: {
            description: 'Users who are set to automatically be added to tickets and other org things.',
            type:        'array of objects (Person)',
            attribute:   'auto_cc_people'
          },
          labels: {
            description: '',
            type:        'array of objects (LabelOrganization)',
            attribute:   'labels',
            properties:  {
              organization: {
                description: '',
                type:        'object (Organization)',
                attribute:   'organization'
              }
            }
          },
          contact_data: {
            description: '',
            type:        'array of objects (OrganizationContactData)',
            attribute:   'contact_data'
          },
          email_domains: {
            description: '',
            type:        'array of objects (OrganizationEmailDomain)',
            attribute:   'email_domains',
            properties:  {
              domain: {
                description: 'The email domain.',
                type:        'string',
                attribute:   'domain'
              },
              organization: {
                description: 'The users organization.',
                type:        'object (Organization)',
                attribute:   'organization'
              }
            }
          },
          date_created: {
            description: 'The date the org was inserted into the system.',
            type:        'DateTime',
            attribute:   'date_created'
          },
          twitter_users: {
            description: '',
            type:        'array of objects (OrganizationTwitterUser)',
            attribute:   'twitter_users',
            properties:  {
              id: {
                description: '',
                type:        'integer',
                attribute:   'id'
              },
              organization: {
                description: '',
                type:        'object (Organization)',
                attribute:   'organization'
              },
              twitter_user_id: {
                description: '',
                type:        'integer',
                attribute:   'twitter_user_id'
              },
              screen_name: {
                description: '@var string',
                type:        'string',
                attribute:   'screen_name'
              },
              is_verified: {
                description: '@var bool',
                type:        'boolean',
                attribute:   'is_verified'
              },
              oauth_token: {
                description: '@var string|null',
                type:        'string',
                attribute:   'oauth_token'
              },
              oauth_token_secret: {
                description: '@var string|null',
                type:        'string',
                attribute:   'oauth_token_secret'
              }
            }
          },
          members: {
            description: '',
            type:        'array of objects (Person)',
            attribute:   'members'
          },
          parent: {
            description: '',
            type:        'object (Organization)',
            attribute:   'parent'
          },
          children: {
            description: '',
            type:        'array of objects (Organization)',
            attribute:   'children'
          },
          tickets: {
            description: '',
            type:        'array of objects (Ticket)',
            attribute:   'tickets'
          }
        }
      },
      linked_chat: {
        description: '',
        type:        'object (ChatConversation)',
        attribute:   'linked_chat',
        properties:  {
          id: {
            description: 'The unique id of chat conversation.',
            type:        'integer',
            attribute:   'id'
          },
          department: {
            description: 'Department which chat was assigned.',
            type:        'object (Department)',
            attribute:   'department'
          },
          labels: {
            description: '',
            type:        'array of objects (LabelChatConversation)',
            attribute:   'labels',
            properties:  {
              chat: {
                description: '',
                type:        'object (ChatConversation)',
                attribute:   'chat'
              }
            }
          },
          subject: {
            description: 'Subject of the chat conversation.',
            type:        'string',
            attribute:   'subject'
          },
          status: {
            description: 'Status of the chat conversation.',
            type:        'string',
            attribute:   'status'
          },
          agent: {
            description: 'If this is a user conversation, this is the agent assigned.',
            type:        'object (Person)',
            attribute:   'agent'
          },
          agent_team: {
            description: 'If this is a team chat, the team it is.',
            type:        'object (AgentTeam)',
            attribute:   'agent_team'
          },
          person: {
            description: 'If this is a user conversation, this is the user who started the chat.',
            type:        'object (Person)',
            attribute:   'person'
          },
          session: {
            description: 'If this is a user convo, this is the users session.',
            type:        'object (Session)',
            attribute:   'session',
            properties:  {
              id: {
                description: 'The unique ID.',
                type:        'integer',
                attribute:   'id'
              },
              auth: {
                description: 'The authcode for the session to verify an id.',
                type:        'string',
                attribute:   'auth'
              },
              interface: {
                description: 'The Interface the session is for.',
                type:        'string',
                attribute:   'interface'
              },
              person: {
                description: '',
                type:        'object (Person)',
                attribute:   'person'
              },
              user_agent: {
                description: 'The users user agent string.',
                type:        'string',
                attribute:   'user_agent'
              },
              visitor_id: {
                description: '',
                type:        'string',
                attribute:   'visitor_id'
              },
              ip_address: {
                description: 'The users IP address.',
                type:        'string',
                attribute:   'ip_address'
              },
              data: {
                description: '',
                type:        'string',
                attribute:   'data'
              },
              is_person: {
                description: '',
                type:        'boolean',
                attribute:   'is_person'
              },
              is_bot: {
                description: '',
                type:        'boolean',
                attribute:   'is_bot'
              },
              is_helpdesk: {
                description: '',
                type:        'boolean',
                attribute:   'is_helpdesk'
              },
              active_status: {
                description: '(Agents) Status (available or away).',
                type:        'string',
                attribute:   'active_status'
              },
              is_chat_available: {
                description: '(Agents) Wehn status is available, if they are available for chat.',
                type:        'boolean',
                attribute:   'is_chat_available'
              },
              date_created: {
                description: '',
                type:        'DateTime',
                attribute:   'date_created'
              },
              date_last: {
                description: '',
                type:        'DateTime',
                attribute:   'date_last'
              },
              date_last_page: {
                description: '',
                type:        'DateTime',
                attribute:   'date_last_page'
              }
            }
          },
          visitor_id: {
            description: '',
            type:        'string',
            attribute:   'visitor_id'
          },
          person_name: {
            description: 'User chat: The users name, if they arent a person.',
            type:        'string',
            attribute:   'person_name'
          },
          person_email: {
            description: 'User chat: The users email, if they aren`t a person.',
            type:        'string',
            attribute:   'person_email'
          },
          participants: {
            description: '',
            type:        'array of objects (Person)',
            attribute:   'participants'
          },
          messages: {
            description: '',
            type:        'array of objects (ChatMessage)',
            attribute:   'messages',
            properties:  {
              id: {
                description: 'The unique ID.',
                type:        'integer',
                attribute:   'id'
              },
              author_id: {
                description: 'Author id (legacy).',
                type:        'integer',
                attribute:   'author_id'
              },
              author_type: {
                description: 'Author type (legacy).',
                type:        'string',
                attribute:   'author_type'
              },
              conversation_id: {
                description: 'Conversation id (legacy).',
                type:        'integer',
                attribute:   'conversation_id'
              },
              author: {
                description: 'Person who created the message.',
                type:        'integer id (Person)',
                attribute:   'author'
              },
              content: {
                description: 'The message.',
                type:        'string',
                attribute:   'content'
              },
              is_sys: {
                description: 'Is this a system message? (ended, joined, etc).',
                type:        'boolean',
                attribute:   'is_sys'
              },
              is_user: {
                description: "Is this an user's message? (send from the widget).",
                type:        'boolean',
                attribute:   'is_user'
              },
              is_html: {
                description: 'Is the content an HTML message?',
                type:        'boolean',
                attribute:   'is_html'
              },
              metadata: {
                description: 'Additional data.',
                type:        'array',
                attribute:   'metadata'
              },
              date_created: {
                description: 'Date message was created.',
                type:        'DateTime',
                attribute:   'date_created'
              },
              date_received: {
                description: 'Date message was received.',
                type:        'DateTime',
                attribute:   'date_received'
              }
            }
          },
          custom_data: {
            description: '',
            type:        'array of objects (CustomDataChat)',
            attribute:   'custom_data',
            properties:  {
              conversation: {
                description: 'The conversation the message belongs to.',
                type:        'object (ChatConversation)',
                attribute:   'conversation'
              },
              field: {
                description: '',
                type:        'object (CustomDefChat)',
                attribute:   'field',
                properties:  {
                  choices: {
                    description: '',
                    type:        'array',
                    attribute:   'choices'
                  },
                  widget_type: {
                    description: 'Gets the widget type. This is the same as the type, except if this is a choice\nwe return the real type of field (e.g., checkbox or radio) based on display options.',
                    type:        'string',
                    attribute:   'widget_type'
                  },
                  id: {
                    description: 'The unique ID.',
                    type:        'integer',
                    attribute:   'id'
                  },
                  title: {
                    description: 'The title.',
                    type:        'string',
                    attribute:   'title'
                  },
                  description: {
                    description: 'The description.',
                    type:        'string',
                    attribute:   'description'
                  },
                  options: {
                    description: 'Options for the field.',
                    type:        'array',
                    attribute:   'options'
                  },
                  is_user_enabled: {
                    description: 'Can the field be viewed by the user?',
                    type:        'boolean',
                    attribute:   'is_user_enabled'
                  },
                  is_enabled: {
                    description: 'True if field is enabled.',
                    type:        'boolean',
                    attribute:   'is_enabled'
                  },
                  display_order: {
                    description: 'Obviously it is field`s display order.',
                    type:        'integer',
                    attribute:   'display_order'
                  },
                  is_agent_field: {
                    description: 'Is this field associated with agents only.',
                    type:        'boolean',
                    attribute:   'is_agent_field'
                  },
                  parent: {
                    description: '',
                    type:        'object (CustomDefChat)',
                    attribute:   'parent'
                  },
                  children: {
                    description: 'Field children.',
                    type:        'array of objects (CustomDefChat)',
                    attribute:   'children'
                  }
                }
              },
              root_field: {
                description: '',
                type:        'object (CustomDefChat)',
                attribute:   'root_field'
              }
            }
          },
          rating_response_time: {
            description: '',
            type:        'integer',
            attribute:   'rating_response_time'
          },
          rating_overall: {
            description: '',
            type:        'integer',
            attribute:   'rating_overall'
          },
          rating_comment: {
            description: '',
            type:        'string',
            attribute:   'rating_comment'
          },
          is_agent: {
            description: 'Is this an agent chat.',
            type:        'boolean',
            attribute:   'is_agent'
          },
          is_window: {
            description: "If the chat is popped out into a window.\nThis is used to make sure the JS widget on pages doesn't load again.",
            type:        'boolean',
            attribute:   'is_window'
          },
          date_created: {
            description: 'Date when chat was started.',
            type:        'DateTime',
            attribute:   'date_created'
          },
          date_user_waiting: {
            description: 'Since when the user has started waiting (i.e., time the assignment was 0).',
            type:        'DateTime',
            attribute:   'date_user_waiting'
          },
          date_assigned: {
            description: '',
            type:        'DateTime',
            attribute:   'date_assigned'
          },
          date_agent_typing: {
            description: 'Date when agent typed last time.',
            type:        'DateTime',
            attribute:   'date_agent_typing'
          },
          date_first_agent_message: {
            description: '',
            type:        'DateTime',
            attribute:   'date_first_agent_message'
          },
          date_ended: {
            description: 'Date when chat was ended.',
            type:        'DateTime',
            attribute:   'date_ended'
          },
          total_to_ended: {
            description: '',
            type:        'integer',
            attribute:   'total_to_ended'
          },
          ended_by: {
            description: 'Who ended the chat.',
            type:        'string',
            attribute:   'ended_by'
          },
          should_send_transcript: {
            description: 'True if transcript should be send.',
            type:        'boolean',
            attribute:   'should_send_transcript'
          },
          date_transcript_sent: {
            description: 'Date when transcript was sent.',
            type:        'DateTime',
            attribute:   'date_transcript_sent'
          },
          email_validation_code: {
            description: '',
            type:        'string',
            attribute:   'email_validation_code'
          },
          email_validated: {
            description: '',
            type:        'boolean',
            attribute:   'email_validated'
          }
        }
      },
      attachments: {
        description: '',
        type:        'array of objects (TicketAttachment)',
        attribute:   'attachments',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          ticket: {
            description: 'Ticket this attachment belongs to.',
            type:        'integer id (Ticket)',
            attribute:   'ticket'
          },
          person: {
            description: 'Who created the attachment.',
            type:        'integer id (Person)',
            attribute:   'person'
          },
          blob: {
            description: 'Actual attachment.',
            type:        'object (Blob)',
            attribute:   'blob',
            properties:  {
              id: {
                description: '',
                type:        'integer',
                attribute:   'id'
              },
              sys_name: {
                description: 'A unique system name for the blob.',
                type:        'string',
                attribute:   'sys_name'
              },
              original_blob: {
                description: 'Sometimes we might have multiple versions of a file. For example, if a file has been\ncropped then the cropped file is saved as its own blob, but the original\nis linked here.',
                type:        'object (Blob)',
                attribute:   'original_blob'
              },
              storage_loc: {
                description: 'The storage adapter that knows how to load this file.',
                type:        'string',
                attribute:   'storage_loc'
              },
              storage_loc_pref: {
                description: "The preferred storage adapter. This is used to mark when we want to move\na file from one storage location to another. For example, if an upload\nto S3 failed and we saved the file in the database instead,\nthen the $storage_loc would be 'db' but $storage_loc_pref would be 's3'.\n\nThe cron jobs will look for when these two values don't match and will\nattempt to move resources gradually.",
                type:        'string',
                attribute:   'storage_loc_pref'
              },
              storage_loc_specific: {
                description: "A set storage adapter. This is used to 'set' a storage adapter that will\nbe used even if the system would normally use a different one.\n\nThe main usage for this is to store things like logfiles in the database\ninstead of something like s3. DeskPRO saves lots of logfiles, so its\npretty inefficient to send off lots of small logfiles to s3.",
                type:        'string',
                attribute:   'storage_loc_specific'
              },
              save_path: {
                description: "The path to the file if it's not stored in the database.",
                type:        'string',
                attribute:   'save_path'
              },
              file_url: {
                description: 'The HTTP link to download the file.',
                type:        'string',
                attribute:   'file_url'
              },
              filename: {
                description: 'The original filename.',
                type:        'string',
                attribute:   'filename'
              },
              filesize: {
                description: 'The file size.',
                type:        'integer',
                attribute:   'filesize'
              },
              content_type: {
                description: 'The files mimetype.',
                type:        'string',
                attribute:   'content_type'
              },
              authcode: {
                description: '',
                type:        'string',
                attribute:   'authcode'
              },
              blob_hash: {
                description: '',
                type:        'string',
                attribute:   'blob_hash'
              },
              is_media_upload: {
                description: 'Is this a media upload (appears in the media browser etc). These are files that were\nuploaded and are attached to things.',
                type:        'boolean',
                attribute:   'is_media_upload'
              },
              title: {
                description: 'The title of this file used in interfaces if its a media upload.',
                type:        'string',
                attribute:   'title'
              },
              dim_w: {
                description: 'If this type of file has dimentions, the width.',
                type:        'integer',
                attribute:   'dim_w'
              },
              dim_h: {
                description: 'If this type of file has dimentions, the height.',
                type:        'integer',
                attribute:   'dim_h'
              },
              date_created: {
                description: '',
                type:        'DateTime',
                attribute:   'date_created'
              },
              is_temp: {
                description: '',
                type:        'boolean',
                attribute:   'is_temp'
              },
              labels: {
                description: '',
                type:        'array of objects (LabelBlob)',
                attribute:   'labels',
                properties:  {
                  blob: {
                    description: '',
                    type:        'object (Blob)',
                    attribute:   'blob'
                  }
                }
              }
            }
          },
          message: {
            description: 'Message - holder of this attachment.',
            type:        'integer id (TicketMessage)',
            attribute:   'message'
          },
          is_agent_note: {
            description: 'True if this attachmen just a note.',
            type:        'boolean',
            attribute:   'is_agent_note'
          },
          is_inline: {
            description: 'Is this attachment is embed in message.',
            type:        'boolean',
            attribute:   'is_inline'
          }
        }
      },
      access_codes: {
        description: '',
        type:        'array of objects (TicketAccessCode)',
        attribute:   'access_codes',
        properties:  {
          id: {
            description: '',
            type:        'integer',
            attribute:   'id'
          },
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          person: {
            description: '',
            type:        'object (Person)',
            attribute:   'person'
          },
          auth: {
            description: '',
            type:        'string',
            attribute:   'auth'
          }
        }
      },
      messages: {
        description: '',
        type:        'array of objects (TicketMessage)',
        attribute:   'messages',
        properties:  {
          id: {
            description: 'The unique id of message.',
            type:        'integer',
            attribute:   'id'
          },
          ticket: {
            description: 'Ticket with which this message is associated.',
            type:        'integer id (Ticket)',
            attribute:   'ticket'
          },
          person: {
            description: 'Person this message was sent by.',
            type:        'integer id (Person)',
            attribute:   'person'
          },
          email_source: {
            description: 'Info about email source, if message comes from such source.',
            type:        'integer id (EmailSource)',
            attribute:   'email_source'
          },
          attachments: {
            description: 'Items attached to the ticket.',
            type:        'array of integer ids (TicketAttachment)',
            attribute:   'attachments'
          },
          date_created: {
            description: 'Date when message was created.',
            type:        'DateTime',
            attribute:   'date_created'
          },
          is_agent_note: {
            description: 'Is this message agent note?',
            type:        'integer',
            attribute:   'is_agent_note'
          },
          creation_system: {
            description: 'How this message was created.',
            type:        'string',
            attribute:   'creation_system'
          },
          ip_address: {
            description: 'An ip address from which message was sent.',
            type:        'string',
            attribute:   'ip_address'
          },
          visitor_id: {
            description: 'Unique ID of visitor left this message.',
            type:        'string',
            attribute:   'visitor_id'
          },
          hostname: {
            description: 'Host from which message was left.',
            type:        'string',
            attribute:   'hostname'
          },
          geo_country: {
            description: 'Country message is from.',
            type:        'string',
            attribute:   'geo_country'
          },
          email: {
            description: 'The email address the user sent the email from (gateway messages only).\nThis is a perm record and doesnt change even if the user changes/deletes their email\naddress.',
            type:        'string',
            attribute:   'email'
          },
          message_hash: {
            description: 'An unique hash of message.',
            type:        'string',
            attribute:   'message_hash'
          },
          primary_translation: {
            description: 'The primary translation is the one sent to the user.',
            type:        'integer id (TicketMessageTranslated)',
            attribute:   'primary_translation'
          },
          message: {
            description: 'The message, will be in HTML!',
            type:        'string',
            attribute:   'message'
          },
          message_full: {
            description: "This is the full message, including all quotes/cut content.\nThis will still be the HTMLPurifier'ed content (so it's safe),\nit's just the message before it's been run through the cutter.",
            type:        'string',
            attribute:   'message_full'
          },
          message_raw: {
            description: 'This is the full raw message content. It has not been passed through\nany HTML cleaning process.s.',
            type:        'string',
            attribute:   'message_raw'
          },
          show_full_hint: {
            description: 'A hint to say if we should show message_full by default. We do this when\nwe detect that the user has replied to a message inline rather than above the cut line.',
            type:        'boolean',
            attribute:   'show_full_hint'
          },
          lang_code: {
            description: 'The set/detected lang code.',
            type:        'string',
            attribute:   'lang_code'
          }
        }
      },
      sms_messages: {
        description: '',
        type:        'array of objects (TicketSms)',
        attribute:   'sms_messages',
        properties:  {
          id: {
            description: '',
            type:        'integer',
            attribute:   'id'
          },
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          person: {
            description: '',
            type:        'object (Person)',
            attribute:   'person'
          },
          sms_account: {
            description: '',
            type:        'object (SmsAccount)',
            attribute:   'sms_account',
            properties:  {
              id: {
                description: 'The unique ID.',
                type:        'integer',
                attribute:   'id'
              },
              type: {
                description: '',
                type:        'string',
                attribute:   'type'
              },
              params: {
                description: '',
                type:        'array',
                attribute:   'params'
              },
              identifier: {
                description: '',
                type:        'string',
                attribute:   'identifier'
              },
              phone_number: {
                description: '',
                type:        'object (PhoneNumber)',
                attribute:   'phone_number',
                properties:  {
                  id: {
                    description: 'The unique ID.',
                    type:        'integer',
                    attribute:   'id'
                  },
                  person: {
                    description: '',
                    type:        'object (Person)',
                    attribute:   'person'
                  },
                  number: {
                    description: 'The number, stored in E.164 string format, ie. +19021111111.',
                    type:        'string',
                    attribute:   'number'
                  },
                  label: {
                    description: 'A human-defined (optional) label to describe what this phone number is.',
                    type:        'string',
                    attribute:   'label'
                  },
                  ext: {
                    description: 'An extension for the number - optional.',
                    type:        'string',
                    attribute:   'ext'
                  },
                  region: {
                    description: 'The ISO 3166-1 country/region code of the phone number (2 char).',
                    type:        'string',
                    attribute:   'region'
                  },
                  guessed_type: {
                    description: '',
                    type:        'integer',
                    attribute:   'guessed_type'
                  },
                  date_created: {
                    description: '',
                    type:        'DateTime',
                    attribute:   'date_created'
                  }
                }
              },
              is_enabled: {
                description: '',
                type:        'boolean',
                attribute:   'is_enabled'
              },
              is_connected: {
                description: '',
                type:        'boolean',
                attribute:   'is_connected'
              },
              is_tested: {
                description: '',
                type:        'boolean',
                attribute:   'is_tested'
              },
              test_code: {
                description: '',
                type:        'string',
                attribute:   'test_code'
              }
            }
          },
          job: {
            description: '',
            type:        'object (Job)',
            attribute:   'job',
            properties:  {
              id: {
                description: 'The unique job id.',
                type:        'integer',
                attribute:   'id'
              },
              type: {
                description: 'The job type, used by the Job Router to find the right Job Processor.',
                type:        'string',
                attribute:   'type'
              },
              status: {
                description: 'The status of the job.',
                type:        'string',
                attribute:   'status'
              },
              status_code: {
                description: 'Any code to further classify the status.',
                type:        'string',
                attribute:   'status_code'
              },
              date_touch: {
                description: 'Date the last time a process "touched" this ticket.',
                type:        'DateTime',
                attribute:   'date_touch'
              },
              date_created: {
                description: 'Date this job entered the "jobs" table.',
                type:        'DateTime',
                attribute:   'date_created'
              },
              date_last_try: {
                description: 'Last time we processed this job.',
                type:        'DateTime',
                attribute:   'date_last_try'
              },
              date_next_try: {
                description: "If this DateTime is in the future, it won't be selected for execution.",
                type:        'DateTime',
                attribute:   'date_next_try'
              }
            }
          },
          date_created: {
            description: '',
            type:        'DateTime',
            attribute:   'date_created'
          },
          from_number: {
            description: "Permanent record of the phone number that sent this SMS. (Won't change even if Person changes their phone number).",
            type:        'string',
            attribute:   'from_number'
          },
          to_number: {
            description: 'Permanent record of the phone number that this SMS was sent to.',
            type:        'string',
            attribute:   'to_number'
          },
          message: {
            description: 'The SMS message.',
            type:        'string',
            attribute:   'message'
          },
          direction: {
            description: 'Just a system flag for reporting.',
            type:        'string',
            attribute:   'direction'
          }
        }
      },
      custom_data: {
        description: '',
        type:        'array of objects (CustomDataTicket)',
        attribute:   'custom_data',
        properties:  {
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          field: {
            description: '',
            type:        'object (CustomDefTicket)',
            attribute:   'field',
            properties:  {
              choices: {
                description: '',
                type:        'array',
                attribute:   'choices'
              },
              widget_type: {
                description: 'Gets the widget type. This is the same as the type, except if this is a choice\nwe return the real type of field (e.g., checkbox or radio) based on display options.',
                type:        'string',
                attribute:   'widget_type'
              },
              id: {
                description: 'The unique ID.',
                type:        'integer',
                attribute:   'id'
              },
              title: {
                description: 'The title.',
                type:        'string',
                attribute:   'title'
              },
              description: {
                description: 'The description.',
                type:        'string',
                attribute:   'description'
              },
              options: {
                description: 'Options for the field.',
                type:        'array',
                attribute:   'options'
              },
              is_user_enabled: {
                description: 'Can the field be viewed by the user?',
                type:        'boolean',
                attribute:   'is_user_enabled'
              },
              is_enabled: {
                description: 'True if field is enabled.',
                type:        'boolean',
                attribute:   'is_enabled'
              },
              display_order: {
                description: 'Obviously it is field`s display order.',
                type:        'integer',
                attribute:   'display_order'
              },
              is_agent_field: {
                description: 'Is this field associated with agents only.',
                type:        'boolean',
                attribute:   'is_agent_field'
              },
              parent: {
                description: '',
                type:        'integer id (CustomDefTicket)',
                attribute:   'parent'
              }
            }
          },
          root_field: {
            description: '',
            type:        'object (CustomDefTicket)',
            attribute:   'root_field'
          }
        }
      },
      labels: {
        description: '',
        type:        'array of objects (LabelTicket)',
        attribute:   'labels',
        properties:  {
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          }
        }
      },
      sent_to_address: {
        description: "The email address the ticket was sent to if it came in via a gateway.\nThis is a full string (e.g., including CC's) of the original.",
        type:        'string',
        attribute:   'sent_to_address'
      },
      email_account: {
        description: 'The gateway this ticket originated from.',
        type:        'object (EmailAccount)',
        attribute:   'email_account',
        properties:  {
          id: {
            description: '',
            type:        'integer',
            attribute:   'id'
          },
          account_type: {
            description: '',
            type:        'string',
            attribute:   'account_type'
          },
          is_enabled: {
            description: '',
            type:        'boolean',
            attribute:   'is_enabled'
          },
          address: {
            description: '',
            type:        'string',
            attribute:   'address'
          },
          other_addresses: {
            description: '',
            type:        'array of strings',
            attribute:   'other_addresses'
          },
          options: {
            description: 'Misc options or flags that can be used by whatever uses this account.',
            type:        'array',
            attribute:   'options'
          },
          date_created: {
            description: '',
            type:        'DateTime',
            attribute:   'date_created'
          },
          date_read_start: {
            description: '',
            type:        'DateTime',
            attribute:   'date_read_start'
          },
          date_last_incoming: {
            description: '',
            type:        'DateTime',
            attribute:   'date_last_incoming'
          },
          is_read_active: {
            description: '',
            type:        'boolean',
            attribute:   'is_read_active'
          }
        }
      },
      email_account_address: {
        description: 'The email address (from list of to/cc) that matched with the email account.',
        type:        'string',
        attribute:   'email_account_address'
      },
      creation_system: {
        description: '',
        type:        'string',
        attribute:   'creation_system'
      },
      creation_system_option: {
        description: 'Optional information about the creation system. For example, source URL the ticket came from.',
        type:        'string',
        attribute:   'creation_system_option'
      },
      ticket_hash: {
        description: '',
        type:        'string',
        attribute:   'ticket_hash'
      },
      status: {
        description: '',
        type:        'string',
        attribute:   'status'
      },
      hidden_status: {
        description: '',
        type:        'string',
        attribute:   'hidden_status'
      },
      is_hold: {
        description: 'Is the ticket on hold?',
        type:        'boolean',
        attribute:   'is_hold'
      },
      urgency: {
        description: '',
        type:        'integer',
        attribute:   'urgency'
      },
      feedback_rating: {
        description: '',
        type:        'integer',
        attribute:   'feedback_rating'
      },
      date_feedback_rating: {
        description: '',
        type:        'DateTime',
        attribute:   'date_feedback_rating'
      },
      date_created: {
        description: '',
        type:        'DateTime',
        attribute:   'date_created'
      },
      date_resolved: {
        description: '',
        type:        'DateTime',
        attribute:   'date_resolved'
      },
      date_archived: {
        description: '',
        type:        'DateTime',
        attribute:   'date_archived'
      },
      date_first_agent_assign: {
        description: '',
        type:        'DateTime',
        attribute:   'date_first_agent_assign'
      },
      date_first_agent_reply: {
        description: '',
        type:        'DateTime',
        attribute:   'date_first_agent_reply'
      },
      date_last_agent_reply: {
        description: '',
        type:        'DateTime',
        attribute:   'date_last_agent_reply'
      },
      date_last_user_reply: {
        description: '',
        type:        'DateTime',
        attribute:   'date_last_user_reply'
      },
      date_agent_waiting: {
        description: '',
        type:        'DateTime',
        attribute:   'date_agent_waiting'
      },
      date_user_waiting: {
        description: '',
        type:        'DateTime',
        attribute:   'date_user_waiting'
      },
      date_status: {
        description: '',
        type:        'DateTime',
        attribute:   'date_status'
      },
      total_user_waiting: {
        description: '',
        type:        'integer',
        attribute:   'total_user_waiting'
      },
      total_to_first_reply: {
        description: '',
        type:        'integer',
        attribute:   'total_to_first_reply'
      },
      locked_by_agent: {
        description: '',
        type:        'object (Person)',
        attribute:   'locked_by_agent'
      },
      date_locked: {
        description: '',
        type:        'DateTime',
        attribute:   'date_locked'
      },
      has_attachments: {
        description: '',
        type:        'boolean',
        attribute:   'has_attachments'
      },
      subject: {
        description: '',
        type:        'string',
        attribute:   'subject'
      },
      original_subject: {
        description: '',
        type:        'string',
        attribute:   'original_subject'
      },
      properties: {
        description: '',
        type:        'array',
        attribute:   'properties'
      },
      count_agent_replies: {
        description: '',
        type:        'integer',
        attribute:   'count_agent_replies'
      },
      count_user_replies: {
        description: '',
        type:        'integer',
        attribute:   'count_user_replies'
      },
      worst_sla_status: {
        description: '',
        type:        'string',
        attribute:   'worst_sla_status'
      },
      waiting_times: {
        description: '',
        type:        'array',
        attribute:   'waiting_times'
      },
      participants: {
        description: '',
        type:        'array of objects (TicketParticipant)',
        attribute:   'participants',
        properties:  {
          id: {
            description: '',
            type:        'integer',
            attribute:   'id'
          },
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          person: {
            description: '',
            type:        'object (Person)',
            attribute:   'person'
          },
          access_code: {
            description: '',
            type:        'object (TicketAccessCode)',
            attribute:   'access_code'
          },
          person_email: {
            description: '',
            type:        'object (PersonEmail)',
            attribute:   'person_email'
          },
          default_on: {
            description: 'Default checkbox status of the user.',
            type:        'boolean',
            attribute:   'default_on'
          }
        }
      },
      charges: {
        description: '',
        type:        'array of objects (TicketCharge)',
        attribute:   'charges',
        properties:  {
          id: {
            description: '',
            type:        'integer',
            attribute:   'id'
          },
          charge_time: {
            description: '',
            type:        'integer',
            attribute:   'charge_time'
          },
          amount: {
            description: '',
            type:        'float',
            attribute:   'amount'
          },
          date_created: {
            description: '',
            type:        'DateTime',
            attribute:   'date_created'
          },
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          person: {
            description: '',
            type:        'object (Person)',
            attribute:   'person'
          },
          organization: {
            description: '',
            type:        'object (Organization)',
            attribute:   'organization'
          },
          agent: {
            description: '',
            type:        'object (Person)',
            attribute:   'agent'
          },
          custom_data: {
            description: '',
            type:        'array of objects (CustomDataBilling)',
            attribute:   'custom_data',
            properties:  {
              ticket_charge: {
                description: '',
                type:        'object (TicketCharge)',
                attribute:   'ticket_charge'
              },
              field: {
                description: '',
                type:        'object (CustomDefBilling)',
                attribute:   'field',
                properties:  {
                  choices: {
                    description: '',
                    type:        'array',
                    attribute:   'choices'
                  },
                  widget_type: {
                    description: 'Gets the widget type. This is the same as the type, except if this is a choice\nwe return the real type of field (e.g., checkbox or radio) based on display options.',
                    type:        'string',
                    attribute:   'widget_type'
                  },
                  id: {
                    description: 'The unique ID.',
                    type:        'integer',
                    attribute:   'id'
                  },
                  title: {
                    description: 'The title.',
                    type:        'string',
                    attribute:   'title'
                  },
                  description: {
                    description: 'The description.',
                    type:        'string',
                    attribute:   'description'
                  },
                  options: {
                    description: 'Options for the field.',
                    type:        'array',
                    attribute:   'options'
                  },
                  is_user_enabled: {
                    description: 'Can the field be viewed by the user?',
                    type:        'boolean',
                    attribute:   'is_user_enabled'
                  },
                  is_enabled: {
                    description: 'True if field is enabled.',
                    type:        'boolean',
                    attribute:   'is_enabled'
                  },
                  display_order: {
                    description: 'Obviously it is field`s display order.',
                    type:        'integer',
                    attribute:   'display_order'
                  },
                  is_agent_field: {
                    description: 'Is this field associated with agents only.',
                    type:        'boolean',
                    attribute:   'is_agent_field'
                  },
                  parent: {
                    description: '',
                    type:        'object (CustomDefBilling)',
                    attribute:   'parent'
                  },
                  children: {
                    description: 'Field children.',
                    type:        'array of objects (CustomDefBilling)',
                    attribute:   'children'
                  }
                }
              },
              root_field: {
                description: '',
                type:        'object (CustomDefBilling)',
                attribute:   'root_field'
              }
            }
          }
        }
      },
      ticket_slas: {
        description: '',
        type:        'array of objects (TicketSla)',
        attribute:   'ticket_slas',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          sla_status: {
            description: '',
            type:        'string',
            attribute:   'sla_status'
          },
          warn_date: {
            description: '',
            type:        'DateTime',
            attribute:   'warn_date'
          },
          fail_date: {
            description: '',
            type:        'DateTime',
            attribute:   'fail_date'
          },
          is_completed: {
            description: '',
            type:        'boolean',
            attribute:   'is_completed'
          },
          is_completed_set: {
            description: '',
            type:        'boolean',
            attribute:   'is_completed_set'
          },
          completed_time_taken: {
            description: '',
            type:        'integer',
            attribute:   'completed_time_taken'
          },
          ticket: {
            description: '',
            type:        'integer id (Ticket)',
            attribute:   'ticket'
          },
          sla: {
            description: '',
            type:        'integer id (Sla)',
            attribute:   'sla'
          }
        }
      },
      jira_issues: {
        description: 'linked jira issues.',
        type:        'array of objects (JiraIssue)',
        attribute:   'jira_issues',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          ticket: {
            description: 'The associated DeskPRO ticket.',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          issue_id: {
            description: '',
            type:        'integer',
            attribute:   'issue_id'
          },
          status_id: {
            description: '',
            type:        'integer',
            attribute:   'status_id'
          },
          created: {
            description: 'Export time.',
            type:        'DateTime',
            attribute:   'created'
          }
        }
      },
      problems: {
        description: '',
        type:        'array of objects (Problem)',
        attribute:   'problems',
        properties:  {
          id: {
            description: 'The unique ID.',
            type:        'integer',
            attribute:   'id'
          },
          title: {
            description: 'Problem title.',
            type:        'string',
            attribute:   'title'
          },
          creator: {
            description: 'Person who created the problem.',
            type:        'integer id (Person)',
            attribute:   'creator'
          },
          created: {
            description: 'Date when the problem was created.',
            type:        'DateTime',
            attribute:   'created'
          },
          is_open: {
            description: 'Is problem still has no solution?',
            type:        'boolean',
            attribute:   'is_open'
          },
          tickets: {
            description: 'Tickets associated with problem.',
            type:        'array of integer ids (Ticket)',
            attribute:   'tickets'
          }
        }
      },
      stars: {
        description: '',
        type:        'array of objects (TicketFlagged)',
        attribute:   'stars',
        properties:  {
          ticket: {
            description: '',
            type:        'object (Ticket)',
            attribute:   'ticket'
          },
          person_id: {
            description: '',
            type:        'integer',
            attribute:   'person_id'
          },
          color: {
            description: '',
            type:        'string',
            attribute:   'color'
          }
        }
      }
    }
  },
  reply: {
    description: 'Reply written by the agent.',
    type:        'object (TicketMessage)',
    attribute:   'reply',
    properties:  {
      id: {
        description: 'The unique id of message.',
        type:        'integer',
        attribute:   'id'
      },
      ticket: {
        description: 'Ticket with which this message is associated.',
        type:        'integer id (Ticket)',
        attribute:   'ticket'
      },
      person: {
        description: 'Person this message was sent by.',
        type:        'integer id (Person)',
        attribute:   'person'
      },
      email_source: {
        description: 'Info about email source, if message comes from such source.',
        type:        'integer id (EmailSource)',
        attribute:   'email_source'
      },
      attachments: {
        description: 'Items attached to the ticket.',
        type:        'array of integer ids (TicketAttachment)',
        attribute:   'attachments'
      },
      date_created: {
        description: 'Date when message was created.',
        type:        'DateTime',
        attribute:   'date_created'
      },
      is_agent_note: {
        description: 'Is this message agent note?',
        type:        'integer',
        attribute:   'is_agent_note'
      },
      creation_system: {
        description: 'How this message was created.',
        type:        'string',
        attribute:   'creation_system'
      },
      ip_address: {
        description: 'An ip address from which message was sent.',
        type:        'string',
        attribute:   'ip_address'
      },
      visitor_id: {
        description: 'Unique ID of visitor left this message.',
        type:        'string',
        attribute:   'visitor_id'
      },
      hostname: {
        description: 'Host from which message was left.',
        type:        'string',
        attribute:   'hostname'
      },
      geo_country: {
        description: 'Country message is from.',
        type:        'string',
        attribute:   'geo_country'
      },
      email: {
        description: 'The email address the user sent the email from (gateway messages only).\nThis is a perm record and doesnt change even if the user changes/deletes their email\naddress.',
        type:        'string',
        attribute:   'email'
      },
      message_hash: {
        description: 'An unique hash of message.',
        type:        'string',
        attribute:   'message_hash'
      },
      primary_translation: {
        description: 'The primary translation is the one sent to the user.',
        type:        'integer id (TicketMessageTranslated)',
        attribute:   'primary_translation'
      },
      message: {
        description: 'The message, will be in HTML!',
        type:        'string',
        attribute:   'message'
      },
      message_full: {
        description: "This is the full message, including all quotes/cut content.\nThis will still be the HTMLPurifier'ed content (so it's safe),\nit's just the message before it's been run through the cutter.",
        type:        'string',
        attribute:   'message_full'
      },
      message_raw: {
        description: 'This is the full raw message content. It has not been passed through\nany HTML cleaning process.s.',
        type:        'string',
        attribute:   'message_raw'
      },
      show_full_hint: {
        description: 'A hint to say if we should show message_full by default. We do this when\nwe detect that the user has replied to a message inline rather than above the cut line.',
        type:        'boolean',
        attribute:   'show_full_hint'
      },
      lang_code: {
        description: 'The set/detected lang code.',
        type:        'string',
        attribute:   'lang_code'
      }
    }
  }
};
export const mediaInline = [
  {
    blob_id: 1234,
    name:    'our_logo.png',
    width:   '600px',
    height:  '790px'
  },
  {
    blob_id: 1235,
    name:    'our_logo_small.png',
    width:   '100px',
    height:  '135px'
  },
  {
    blob_id: 1236,
    name:    'button-pic.jpeg',
    width:   '600px',
    height:  '790px'
  },
  {
    blob_id: 1237,
    name:    'button-pic2.png',
    width:   '600px',
    height:  '790px'
  },
];
export const mediaAttachments = [
  {
    blob_id:   123,
    name:      'Creating_Articles.pdf',
    mime_type: 'application/pdf',
  },
  {
    blob_id:   124,
    name:      'Help_with_group.pdf',
    mime_type: 'application/msword',
  },
  {
    blob_id:   125,
    name:      'Stats.xls',
    mime_type: 'application/excel',
  },
  {
    blob_id:   126,
    name:      'Banner.png',
    mime_type: 'image/png',
  },
  {
    blob_id:   127,
    name:      'Archive.zip',
    mime_type: 'application/zip',
  }
];
export default emailBlocks;
