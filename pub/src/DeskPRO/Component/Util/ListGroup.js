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

const addGroup = (groups, title, matchFn) => groups.push({
  title: title,
  match: matchFn,
  elements: []
});

export const dateGroupsBuilder = (groups, dateField, groupKeys) => {
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

export const recordGroupsBuilder = (groups, records, titleField, taskField, emptyTitle = null) => {
  records.forEach(record => addGroup(
    groups,
    record.get(titleField),
    task => task.get(taskField) === record.get('id'))
  );

  if (emptyTitle) {
    addGroup(groups, emptyTitle, () => true);
  }
};

export const groupCollection = (groups, collection) => {
  let filtered = collection;
  groups.forEach(group =>
    filtered.forEach((task, index) => {
      if (group.match(task)) {
        group.elements.push(task);
        filtered = filtered.delete(index);
      }
    })
  );

  return groups;
};