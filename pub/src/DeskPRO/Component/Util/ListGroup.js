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
  thisYear: {title: 'This Year', match: date => moment().isSame(date, 'year')},
  other: {title: 'Other', match: date => moment().isAfter(date, 'year')}
};

const futureDates = [
  'hour',
  'today',
  'tomorrow',
  'thisWeek',
  'nextWeek',
  'thisMonth',
  'nextMonth',
  'thisYear',
  'other'
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

const addGroup = (groups, title, matchFn) => groups.push({
  title: title,
  match: matchFn,
  elements: []
});

const dateGroupsBuilder = (groups, dateField, groupKeys) => {
  groupKeys.forEach(groupKey => {
    const dateGroup = dateGroups[groupKey];
    addGroup(
      groups,
      dateGroup.title,
      task => dateGroup.match(task.get(dateField))
    );
  });

  addGroup(groups, 'Other', () => true);
};

const recordGroupsBuilder = (groups, records, titleField, refField) => {
  records.forEach(record => addGroup(
    groups,
    record.get(titleField),
    item => {
      const id = record.get('id');
      const value = item.get(refField);

      return value && typeof value === 'object' ? value.includes(id) : value === id;
    }
  ));
};

const addDateGroups = (groups, groupConfig) => {
  let dateGroupKeys = groupConfig.dateGroupKeys;
  if (dateGroupKeys === 'future') {
    dateGroupKeys = futureDates;
  } else if (dateGroupKeys === 'past') {
    dateGroupKeys = pastDates;
  }

  dateGroupsBuilder(groups, groupConfig.refField, dateGroupKeys);
};

const addRecordGroups = (groups, groupConfig) => {
  if (Array.isArray(groupConfig.records)) {
    groupConfig.records.forEach(childGroupConfig => addRecordGroups(groups, childGroupConfig));
  } else {
    recordGroupsBuilder(groups, groupConfig.records, groupConfig.titleField, groupConfig.refField);
  }
  if (groupConfig.emptyGroup) {
    addGroup(groups, groupConfig.emptyGroup, () => true);
  }
};

const getGroups = ({groupKey, options = []}) => {
  const groupConfig = options[groupKey];
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
    filtered.forEach((item, index) => {
      if (group.match(item)) {
        group.elements.push(item);
        filtered = filtered.delete(index);
      }
    })
  );

  return groups;
};