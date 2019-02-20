import Immutable from 'immutable';

export const regex = /\[([^\]]+)\]/g;

export function transformLabels(labels) {
  return labels.map(label => label.set('active', false)).toList();
}

export function countActiveLabels(labels) {
  return labels.reduce((reduced, value) => reduced + (value.get('active') ? 1 : 0), 0);
}

export function activateLabel(clickedLabel, labels, toggle = true) {
  const entry = labels.findEntry(value => value.get('label') === clickedLabel);
  let newActiveLabels = countActiveLabels(labels);
  if (entry) {
    const index = entry[0];
    const label = entry[1].set('active', !toggle ? true : !entry[1].get('active'));
    const newLabels = labels.set(index, label);
    newActiveLabels = countActiveLabels(newLabels);
    return { newLabels, newActiveLabels, label };
  }
  return { newLabels: labels, newActiveLabels };
}


export function transformReportData(report) {
  const queryParts = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
  return {
    query:          report.get('query', ''),
    title:          report.get('title'),
    labels:         report.get('labels', Immutable.List()).toArray(),
    desc:           report.get('description', ''),
    display_types:  report.get('display_types', Immutable.List()).toJS(),
    select:         queryParts.get('select', ''),
    from:           queryParts.get('from', ''),
    where:          queryParts.get('where', ''),
    split_by:       queryParts.get('split_by', ''),
    group_by:       queryParts.get('group_by', ''),
    with_rollup:    queryParts.get('with_rollup', ''),
    order_by:       queryParts.get('order_by', ''),
    offset:         queryParts.get('offset', ''),
    limit:          queryParts.get('limit', ''),
    vars:           report.get('variables', Immutable.List()).toJS(),
    id:             report.get('id', 0),
    extended_query: report.get('extended_query', false)
  };
}

export function transformReportDataToApi(reportData, error = false) {
  const data = {
    display_types: reportData.display_types
  };

  if (!reportData.displayOnly) {
    data.title         = reportData.title;
    data.description   = reportData.desc;
    data.display_types = reportData.display_types;
    data.variables     = reportData.vars;
    data.labels        = reportData.labels;
    data.input_mode    = reportData.inputMode || 'form';
    data.query         = reportData.raw;
    data.query_parts   = {
      select:      reportData.select,
      from:        reportData.from,
      where:       reportData.where,
      split_by:    reportData.split_by,
      group_by:    reportData.group_by,
      with_rollup: reportData.with_rollup,
      order_by:    reportData.order_by,
      limit:       reportData.limit,
      offset:      reportData.offset
    };

    if (!error) {
      if (data.input_mode === 'dpql') {
        delete data.query_parts;
      } else {
        delete data.query;
      }
    }
  }

  return data;
}

export const displayTypes = [
  {
    label: 'Bars',
    value: 'simple_bars'
  },
  {
    label: 'Lines',
    value: 'simple_lines'
  },
  {
    label: 'Area',
    value: 'simple_area'
  },
  {
    label: 'Pie',
    value: 'pie'
  },
  {
    label: 'Table',
    value: 'table'
  },
  {
    label: 'Stat',
    value: 'simple_stat'
  },
  {
    label: 'Gauge',
    value: 'gauge'
  },
  {
    label: 'Bubble',
    value: 'bubble'
  }
];

export const varTypes = [
  {
    label: 'Date',
    value: 'dates'
  },
  {
    label: 'Status',
    value: 'statuses'
  },
  {
    label: 'Grouping fields',
    value: 'fields'
  },
  {
    label: 'Ordering fields',
    value: 'orders'
  },
  {
    label: 'Value',
    value: 'values'
  },
  {
    label: 'Ticket custom fields',
    value: 'ticket_custom_fields'
  },
  {
    label: 'Org custom fields',
    value: 'org_custom_fields'
  },
  {
    label: 'Chat custom fields',
    value: 'chat_custom_fields'
  },
  {
    label: 'User custom fields',
    value: 'user_custom_fields'
  },
  {
    label: 'Article custom fields',
    value: 'article_custom_fields'
  },
  {
    label: 'Feedback custom fields',
    value: 'feedback_custom_fields'
  },
  {
    label: 'Billing custom fields',
    value: 'billing_custom_fields'
  },
  {
    label: 'Product custom fields',
    value: 'product_custom_fields'
  }
];

export const initHandlebars = (Handlebars) => {
  if (Handlebars.dpHasDoneInit) {
    return;
  }
  Handlebars.dpHasDoneInit = true;
  Handlebars.registerHelper('math', (lval, operator, rval) => {
    const lvalue = parseFloat(lval);
    const rvalue = parseFloat(rval);

    return {
      '+': lvalue + rvalue,
      '-': lvalue - rvalue,
      '*': lvalue * rvalue,
      '/': lvalue / rvalue,
      '%': lvalue % rvalue
    }[operator];
  });

  Handlebars.registerHelper('formatNumber', (value, info = {}) => {
    const numValue = Number(value);
    if (isNaN(numValue)) {
      return value;
    }
    try {
      return numValue.toLocaleString('en-US', info.hash || {});
    } catch (e) {
      return value;
    }
  });

  Handlebars.registerHelper('formatCurrency', (value, currency, info = {}) => {
    const numValue = Number(value);
    if (isNaN(numValue)) {
      return value;
    }
    try {
      const options = info.hash || {};
      options.style = 'currency';
      options.currency = currency;
      return numValue.toLocaleString('en-US', options);
    } catch (e) {
      return value;
    }
  });

  Handlebars.registerHelper('formatPercent', (value) => {
    const numValue = Number(value);
    if (isNaN(numValue)) {
      return value;
    }

    return `${parseInt(value, 10)}%`;
  });
};
