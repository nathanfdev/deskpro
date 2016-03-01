import moment from 'moment';
import invariant from 'invariant';
import Immutable from 'immutable';

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

const createGroup = (title, updateData, match, sortBy) => ({title, match, updateData, sortBy, elements: []});
const dateGroupsBuilder = (groups, { refField, dateGroupKeys }) => {
  dateGroupKeys.forEach(groupKey => {
    const { title, compareDate, match } = dateGroups[groupKey];
    const updateData = {[refField]: compareDate.format()};
    const matchItem = item => match(item.get(refField), compareDate);

    groups.push(createGroup(title, updateData, matchItem));
  });

  const emptyUpdateData = {[refField]: null};
  groups.push(createGroup('Other', emptyUpdateData, () => true));
};

const recordGroupsBuilder = (groups, { records, titleField, refField, collection, sortBy }) => {
  records.forEach(record => {
    const id = record.get('id');
    const updateData = {[refField]: collection ? [id] : id};
    const matchItem = item => {
      const value = item.get(refField);
      return value && typeof value === 'object' ? value.includes(id) : value === id;
    };

    groups.push(createGroup(record.get(titleField), updateData, matchItem, sortBy));
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
  const { records, refField, emptyGroup, collection, sortBy } = groupConfig;

  if (Array.isArray(records)) {
    records.forEach(childGroupConfig => addRecordGroups(groups, {...childGroupConfig, collection, sortBy}));
  } else {
    recordGroupsBuilder(groups, groupConfig);
  }

  if (emptyGroup) {
    const emptyUpdateData = {};
    if (Array.isArray(records)) {
      const newValue = collection ? [] : null;
      records.forEach(childGroupConfig => emptyUpdateData[childGroupConfig.refField] = newValue);
    } else {
      emptyUpdateData[refField] = null;
    }

    groups.push(createGroup(emptyGroup, emptyUpdateData, () => true, sortBy));
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
  const groups = getGroups(groupConfig);
  invariant(Immutable.Iterable.isIterable(collection), 'Invalid type of collection');

  for (let i = 0; i < groups.length; i++) {
    let group = groups[i];
    collection = collection.filter(function(item){
      return !(group.match(item) && group.elements.push(item));
    });
    if (!collection.size) {
      break;
    }

    if (group.sortBy) {
      group.elements.sort(group.sortBy);
    }
  }

  return groups;
};