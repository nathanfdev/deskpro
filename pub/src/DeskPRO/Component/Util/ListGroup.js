import moment from 'moment';

const dateGroups = {
  hour: {title: 'This Hour', match: date => moment().isSame(date, 'hour')},
  yesterday: {title: 'Yesterday', match: date => moment().subtract(1, 'day').isSame(date, 'day')},
  today: {title: 'Today', match: date => moment().isSame(date, 'day')},
  tomorrow: {title: 'Tomorrow', match: date => moment().add(1, 'day').isSame(date, 'day')},

  lastWeek: {title: 'Last Week', match: date => moment().subtract(1, 'week').isSame(date, 'week')},
  thisWeek: {title: 'This Week', match: date => moment().isSame(date, 'week')},
  nextWeek: {title: 'Next Week', match: date => moment().add('week').isSame(date, 'week')},

  lastMonth: {title: 'Last Month', match: date => moment().subtract(1, 'month').isSame(date, 'month')},
  thisMonth: {title: 'This Month', match: date => moment().isSame(date, 'month')},
  nextMonth: {title: 'Next Month', match: date => moment().add(1, 'month').isSame(date, 'month')},

  older: {title: 'Older', match: date => moment().isBefore(date, 'year')},
  thisYear: {title: 'This Year', match: date => moment().isSame(date, 'year')}
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

const createGroup = (title, matchFn) => ({
  title: title,
  match: matchFn,
  elements: []
});

const dateGroupsBuilder = (groups, { refField, dateGroupKeys }) => {
  dateGroupKeys.forEach(groupKey => {
    const dateGroup = dateGroups[groupKey];
    groups.push(createGroup(
      dateGroup.title,
      task => dateGroup.match(task.get(refField))
    ));
  });

  groups.push(createGroup('Other', () => true));
};

const recordGroupsBuilder = (groups, { records, titleField, refField }) => {
  records.forEach(record => groups.push(createGroup(
    record.get(titleField),
    item => {
      const id = record.get('id');
      const value = item.get(refField);

      return value && typeof value === 'object' ? value.includes(id) : value === id;
    }
  )));
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
  const { records, emptyGroup } = groupConfig;

  if (Array.isArray(records)) {
    records.forEach(childGroupConfig => addRecordGroups(groups, childGroupConfig));
  } else {
    recordGroupsBuilder(groups, groupConfig);
  }

  if (emptyGroup) {
    groups.push(createGroup(emptyGroup, () => true));
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