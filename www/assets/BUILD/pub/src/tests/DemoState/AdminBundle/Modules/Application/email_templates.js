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
export default emailBlocks;
