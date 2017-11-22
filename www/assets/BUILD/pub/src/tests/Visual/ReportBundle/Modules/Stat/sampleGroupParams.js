export const groupParams = {
  fields: {
    tickets: {
      day_week_resolved: [
        'day of week resolved',
        'ALIAS(DAYNAME(%s.date_resolved), \'Day of Week Resolved\')'
      ],
      day_week_created: [
        'day of week created',
        'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'
      ],
      orgfield6: [
        'organizations\'s Widget Description',
        'ALIAS(%s.organization.custom_data[6], \'Widget Description\')'
      ],
      orgfield7: [
        'organizations\'s Desired Sizes',
        'ALIAS(%s.organization.custom_data[7], \'Desired Sizes\')'
      ],
      ticketfield1: [
        'Flumdiggler',
        'ALIAS(%s.custom_data[1], \'Flumdiggler\')'
      ],
      priority: [
        'priority',
        '%s.priority'
      ],
      agent: [
        'agent',
        '%s.agent'
      ],
      personfield11: [
        'creator\'s Manufacture Date',
        'ALIAS(%s.person.custom_data[11], \'Manufacture Date\')'
      ],
      personfield33: [
        'creator\'s Date of Creation',
        'ALIAS(%s.person.custom_data[33], \'Date of Creation\')'
      ],
      day_month_created: [
        'day of month created',
        'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'
      ],
      year_created: [
        'year created',
        'ALIAS(YEAR(%s.date_created), \'Year Created\')'
      ],
      personfield12: [
        'creator\'s Mood today',
        'ALIAS(%s.person.custom_data[12], \'Mood today\')'
      ],
      ticketfield5: [
        'Widget Type',
        'ALIAS(%s.custom_data[5], \'Widget Type\')'
      ],
      personfield24: [
        'creator\'s Music',
        'ALIAS(%s.person.custom_data[24], \'Music\')'
      ],
      ticketfield6: [
        'Widget Description',
        'ALIAS(%s.custom_data[6], \'Widget Description\')'
      ],
      replies: [
        'number of replies',
        'ALIAS(%1$s.count_user_replies + %1$s.count_agent_replies, \'Total Replies\')'
      ],
      sla: [
        'SLA',
        '%s.ticket_slas'
      ],
      month_created: [
        'month created',
        'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'
      ],
      ticketfield7: [
        'Desired Sizes',
        'ALIAS(%s.custom_data[7], \'Desired Sizes\')'
      ],
      date_resolved: [
        'date resolved',
        'ALIAS(DATE(%s.date_resolved), \'Date Resolved\')'
      ],
      sla_status: [
        'SLA status',
        '%s.ticket_slas.sla_status'
      ],
      urgency: [
        'urgency',
        '%s.urgency'
      ],
      personfield1: [
        'creator\'s Owner',
        'ALIAS(%s.person.custom_data[1], \'Owner\')'
      ],
      hour_created: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      personfield19: [
        'creator\'s Dishes',
        'ALIAS(%s.person.custom_data[19], \'Dishes\')'
      ],
      ticketfield11: [
        'Manufacture Date',
        'ALIAS(%s.custom_data[11], \'Manufacture Date\')'
      ],
      ticketfield33: [
        'Delivery Time',
        'ALIAS(%s.custom_data[33], \'Delivery Time\')'
      ],
      agent_replies: [
        'number of agent replies',
        'ALIAS(%s.count_agent_replies, \'Agent Replies\')'
      ],
      ticketfield12: [
        'Reason for Complaint',
        'ALIAS(%s.custom_data[12], \'Reason for Complaint\')'
      ],
      product: [
        'product',
        '%s.product'
      ],
      ticketfield24: [
        'Hotdog Kind',
        'ALIAS(%s.custom_data[24], \'Hotdog Kind\')'
      ],
      personfield5: [
        'creator\'s Widget Type',
        'ALIAS(%s.person.custom_data[5], \'Widget Type\')'
      ],
      personfield6: [
        'creator\'s Widget Description',
        'ALIAS(%s.person.custom_data[6], \'Widget Description\')'
      ],
      personfield7: [
        'creator\'s Desired Sizes',
        'ALIAS(%s.person.custom_data[7], \'Desired Sizes\')'
      ],
      department: [
        'department',
        'ALIAS(STACK_GROUP(%1$s.department, COALESCE(%1$s.department.parent.title, %1$s.department.title)), \'Department\')'
      ],
      organization: [
        'organization',
        '%s.organization'
      ],
      hour_resolved: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      user_replies: [
        'number of user replies',
        'ALIAS(%s.count_user_replies, \'User Replies\')'
      ],
      person: [
        'person',
        '%s.person'
      ],
      ticketfield19: [
        'Suggested Actions',
        'ALIAS(%s.custom_data[19], \'Suggested Actions\')'
      ],
      orgfield11: [
        'organizations\'s Manufacture Date',
        'ALIAS(%s.organization.custom_data[11], \'Manufacture Date\')'
      ],
      orgfield12: [
        'organizations\'s Branches',
        'ALIAS(%s.organization.custom_data[12], \'Branches\')'
      ],
      month_resolved: [
        'month resolved',
        'ALIAS(MONTHNAME(%s.date_resolved), \'Month Resolved\')'
      ],
      date_created: [
        'date created',
        'ALIAS(DATE(%s.date_created), \'Date Created\')'
      ],
      agent_team: [
        'agent team',
        '%s.agent_team'
      ],
      orgfield24: [
        'organizations\'s Product',
        'ALIAS(%s.organization.custom_data[24], \'Product\')'
      ],
      none: [
        'nothing',
        'NULL'
      ],
      language: [
        'language',
        '%s.language'
      ],
      year_resolved: [
        'year resolved',
        'ALIAS(YEAR(%s.date_resolved), \'Year Resolved\')'
      ],
      day_month_resolved: [
        'day of month resolved',
        'ALIAS(DAYOFMONTH(%s.date_resolved), \'Day of Month Resolved\')'
      ],
      orgfield38: [
        'organizations\'s Comment',
        'ALIAS(%s.organization.custom_data[38], \'Comment\')'
      ],
      orgfield1: [
        'organizations\'s Countries',
        'ALIAS(%s.organization.custom_data[1], \'Countries\')'
      ],
      category: [
        'category',
        '%s.category'
      ],
      orgfield19: [
        'organizations\'s Official position',
        'ALIAS(%s.organization.custom_data[19], \'Official position\')'
      ],
      orgfield5: [
        'organizations\'s Widget Type',
        'ALIAS(%s.organization.custom_data[5], \'Widget Type\')'
      ],
      workflow: [
        'workflow',
        '%s.workflow'
      ]
    },
    chats: {
      day_week_created: [
        'day of week created',
        'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'
      ],
      agent: [
        'agent',
        '%s.agent'
      ],
      day_month_created: [
        'day of month created',
        'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'
      ],
      year_created: [
        'year created',
        'ALIAS(YEAR(%s.date_created), \'Year Created\')'
      ],
      month_created: [
        'month created',
        'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'
      ],
      hour_created: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      department: [
        'department',
        '%s.department'
      ],
      person: [
        'person',
        '%s.person'
      ],
      date_created: [
        'date created',
        'ALIAS(DATE(%s.date_created), \'Date Created\')'
      ],
      agent_team: [
        'agent team',
        '%s.agent_team'
      ],
      none: [
        'nothing',
        'NULL'
      ]
    },
    articles: {
      person: [
        'person',
        '%s.person'
      ],
      hour_created: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      day_week_created: [
        'day of week created',
        'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'
      ],
      day_month_created: [
        'day of month created',
        'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'
      ],
      month_created: [
        'month created',
        'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'
      ],
      year_created: [
        'year created',
        'ALIAS(YEAR(%s.date_created), \'Year Created\')'
      ],
      date_created: [
        'date created',
        'ALIAS(DATE(%s.date_created), \'Date Created\')'
      ],
      none: [
        'nothing',
        'NULL'
      ]
    },
    article_comments: {
      hour_created: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      day_week_created: [
        'day of week created',
        'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'
      ],
      day_month_created: [
        'day of month created',
        'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'
      ],
      month_created: [
        'month created',
        'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'
      ],
      year_created: [
        'year created',
        'ALIAS(YEAR(%s.date_created), \'Year Created\')'
      ],
      date_created: [
        'date created',
        'ALIAS(DATE(%s.date_created), \'Date Created\')'
      ],
      none: [
        'nothing',
        'NULL'
      ]
    },
    feedback: {
      day_week_created: [
        'day of week created',
        'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'
      ],
      day_month_created: [
        'day of month created',
        'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'
      ],
      year_created: [
        'year created',
        'ALIAS(YEAR(%s.date_created), \'Year Created\')'
      ],
      month_created: [
        'month created',
        'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'
      ],
      hour_created: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      status: [
        'status',
        'ALIAS(%s.status_category, \'Status\')'
      ],
      person: [
        'person',
        '%s.person'
      ],
      date_created: [
        'date created',
        'ALIAS(DATE(%s.date_created), \'Date Created\')'
      ],
      none: [
        'nothing',
        'NULL'
      ],
      type: [
        'type',
        'ALIAS(%s.category, \'Type\')'
      ],
      category: [
        'category',
        'ALIAS(%s.custom_data[1], \'category\')'
      ]
    },
    feedback_comments: {
      hour_created: [
        'hour created',
        'ALIAS(HOUR(%s.date_created), \'Hour Created\')'
      ],
      day_week_created: [
        'day of week created',
        'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'
      ],
      day_month_created: [
        'day of month created',
        'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'
      ],
      month_created: [
        'month created',
        'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'
      ],
      year_created: [
        'year created',
        'ALIAS(YEAR(%s.date_created), \'Year Created\')'
      ],
      date_created: [
        'date created',
        'ALIAS(DATE(%s.date_created), \'Date Created\')'
      ],
      none: [
        'nothing',
        'NULL'
      ]
    }
  },
  dates: {
    today: [
      'today',
      '%TODAY%'
    ],
    yesterday: [
      'yesterday',
      '%YESTERDAY%'
    ],
    last_year: [
      'last year',
      '%LAST_YEAR%'
    ],
    past_7_days: [
      'in the past 7 days',
      '%PAST_7_DAYS%'
    ],
    past_30_days: [
      'in the past 30 days',
      '%PAST_30_DAYS%'
    ],
    past_hour: [
      'in the past hour',
      '%PAST_HOUR%'
    ],
    this_week: [
      'this week',
      '%THIS_WEEK%'
    ],
    last_month: [
      'last month',
      '%LAST_MONTH%'
    ],
    last_week: [
      'last week',
      '%LAST_WEEK%'
    ],
    past_12_hours: [
      'in the past 12 hours',
      '%PAST_12_HOURS%'
    ],
    ever: [
      'any time',
      '%EVER%'
    ],
    this_year: [
      'this year',
      '%THIS_YEAR%'
    ],
    past_24_hours: [
      'in the past 24 hours',
      '%PAST_24_HOURS%'
    ],
    this_month: [
      'this month',
      '%THIS_MONTH%'
    ]
  },
  statuses: {
    tickets: {
      awaiting_user: [
        'awaiting user',
        '%s.status = \'awaiting_user\''
      ],
      awaiting_agent: [
        'awaiting agent',
        '%s.status = \'awaiting_agent\''
      ],
      unresolved: [
        'unresolved',
        '%s.status IN (\'awaiting_user\', \'awaiting_agent\')'
      ],
      resolved: [
        'resolved',
        '%s.status IN (\'resolved\', \'archived\')'
      ],
      hidden: [
        'hidden',
        '%s.status = \'hidden\''
      ],
      any: [
        'with any status',
        '1'
      ]
    }
  },
  orders: {
    tickets: {
      date_created_asc: [
        'date created (ascending)',
        '%s.date_created ASC'
      ],
      date_created_desc: [
        'date created (descending)',
        '%s.date_created DESC'
      ],
      last_agent_reply_asc: [
        'last agent reply (ascending)',
        '%s.date_last_agent_reply ASC'
      ],
      last_agent_reply_desc: [
        'last agent reply (descending)',
        '%s.date_last_agent_reply DESC'
      ],
      last_user_reply_asc: [
        'last user reply (ascending)',
        '%s.date_last_user_reply ASC'
      ],
      last_user_reply_desc: [
        'last user reply (descending)',
        '%s.date_last_user_reply DESC'
      ],
      total_waiting_asc: [
        'total waiting time (ascending)',
        '%s.total_user_waiting ASC'
      ],
      total_waiting_desc: [
        'total waiting time (descending)',
        '%s.total_user_waiting DESC'
      ]
    }
  }
};