import moment from 'moment';

const dateGroups = {
  hour: {
    title: 'This Hour',
    compareDate: moment().startOf('hour'),
    match: (date, compareDate) => compareDate.isSame(date, 'hour')
  },

  yesterday: {
    title: 'Yesterday',
    compareDate: moment().subtract(1, 'day').startOf('day'),
    match: (date, compareDate) => compareDate.isSame(date, 'day')
  },
  today: {
    title: 'Today',
    compareDate: moment().startOf('day'),
    match: (date, compareDate) => compareDate.isSame(date, 'day')
  },
  tomorrow: {
    title: 'Tomorrow',
    compareDate: moment().add(1, 'day').startOf('day'),
    match: (date, compareDate) => compareDate.isSame(date, 'day')
  },

  lastWeek: {
    title: 'Last Week',
    compareDate: moment().subtract(1, 'week').startOf('week'),
    match: (date, compareDate) => compareDate.isSame(date, 'week')
  },
  thisWeek: {
    title: 'This Week',
    compareDate: moment().startOf('week'),
    match: (date, compareDate) => compareDate.isSame(date, 'week')
  },
  nextWeek: {
    title: 'Next Week',
    compareDate: moment().add('week').startOf('week'),
    match: (date, compareDate) => compareDate.isSame(date, 'week')
  },

  lastMonth: {
    title: 'Last Month',
    compareDate: moment().subtract(1, 'month').startOf('month'),
    match: (date, compareDate) => compareDate.isSame(date, 'month')
  },
  thisMonth: {
    title: 'This Month',
    compareDate: moment().startOf('month'),
    match: (date, compareDate) => compareDate.isSame(date, 'month')
  },
  nextMonth: {
    title: 'Next Month',
    compareDate: moment().add(1, 'month').startOf('month'),
    match: (date, compareDate) => compareDate.isSame(date, 'month')
  },

  older: {
    title: 'Older',
    compareDate: moment().subtract(1, 'year').startOf('year'),
    match: (date, compareDate) => compareDate.isSame(date, 'year')
  },
  thisYear: {
    title: 'This Year',
    compareDate: moment().startOf('year'),
    match: (date, compareDate) => compareDate.isSame(date, 'year')
  }
};

const futureDates = [
  'hour',
  'today',
  'tomorrow',
  'thisWeek',
  'nextWeek',
  'thisMonth',
  'nextMonth',
  'thisYear'
];

const pastDates = [
  'hour',
  'today',
  'yesterday',
  'thisWeek',
  'lastWeek',
  'thisMonth',
  'lastMonth',
  'thisYear',
  'older'
];

const createGroup = (title, param, value, matchFn) => ({
  title: title,
  match: matchFn,
  param: param,
  value: value,
  elements: []
});

const dateGroupsBuilder = (groups, { refField, dateGroupKeys }) => {
  dateGroupKeys.forEach(groupKey => {
    const { title, compareDate, match } = dateGroups[groupKey];

    groups.push(createGroup(
      title,
      refField,
      compareDate,
      task => match(task.get(refField), compareDate)
    ));
  });

  groups.push(createGroup(
    'Other',
    refField,
    null,
    () => true
  ));
};

const recordGroupsBuilder = (groups, { records, titleField, refField }) => {
  records.forEach(record => {
    const id = record.get('id');

    groups.push(createGroup(
      record.get(titleField),
      refField,
      id,
      item => {
        const value = item.get(refField);
        return value && typeof value === 'object' ? value.includes(id) : value === id;
      }
    ));
  });
};

const addDateGroups = (groups, groupConfig) => {
  let { dateGroupKeys } = groupConfig;

  if (dateGroupKeys === 'future') {
    dateGroupKeys = futureDates;
  } else if (dateGroupKeys === 'past') {
    dateGroupKeys = pastDates;
  } else if (dateGroupKeys === 'all') {
    dateGroupKeys = Object.keys(dateGroups);
  }

  dateGroupsBuilder(groups, {...groupConfig, dateGroupKeys});
};

const addRecordGroups = (groups, groupConfig) => {
  const { records, refField, emptyGroup } = groupConfig;

  if (Array.isArray(records)) {
    records.forEach(childGroupConfig => addRecordGroups(groups, childGroupConfig));
  } else {
    recordGroupsBuilder(groups, groupConfig);
  }

  if (emptyGroup) {
    groups.push(createGroup(
      emptyGroup,
      refField,
      null,
      () => true
    ));
  }
};

const getGroups = ({groupKey, defaultGroupKey, options = []}) => {
  const groupConfig = options[groupKey] || options[defaultGroupKey];
  const type = groupConfig.type;
  const groups = [];

  if (type === 'record') {
    addRecordGroups(groups, groupConfig);
  } else if (type === 'date') {
    addDateGroups(groups, groupConfig);
  }

  return groups;
};

export const groupCollection = (groupConfig, collection) => {
  let filtered = collection;
  const groups = getGroups(groupConfig);

  groups.forEach(group =>
    filtered.forEach(item => {
      if (group.match(item)) {
        group.elements.push(item);
        filtered = filtered.delete(filtered.indexOf(item));
      }
    })
  );

  return groups;
};