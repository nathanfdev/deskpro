import React from 'react';
import { connect } from 'redux/react';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import * as actions from '../../Actions/publishNavActions'
import { Nav } from './Nav';

@connect(state => {

  // select lists labels depending on their grouping
  const labels = {};
  ['articles', 'news', 'downloads'].forEach(list => {
    switch (state.PublishNav.lists[list].grouped_by) {
      case 'category':
        labels[list] = state.PublishNav.groups.categories[list];
        break;
      case 'author':
        labels[list] = state.PublishNav.groups.authors;
        break;
      case 'period_created':
      case 'period_updated':
        labels[list] = DatePeriods.all;
        break;
    }
  });

  return {labels, lists: state.PublishNav.lists, grouping: state.PublishNav.grouping};
})
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadCounts('articles', this.props.lists.articles.grouped_by));
    this.props.dispatch(actions.loadCounts('news', this.props.lists.news.grouped_by));
    this.props.dispatch(actions.loadCounts('downloads', this.props.lists.downloads.grouped_by));
    this.props.dispatch(actions.loadCategories());
  }

  render() {
    const onGroupingChange = (listName) => this.onGroupingChange(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);

    return (
      <Nav
        lists={this.props.lists}
        labels={this.props.labels}
        grouping={this.props.grouping}
        onGroupingChange={onGroupingChange}
        toggleGroupingVisibility={toggleGroupingVisibility}
      />
    );
  }

  toggleGroupingVisibility(listName) {
    return function(e) {
      e.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    }
  }

  onGroupingChange(listName) {
    return function(e) {
      const options = e.target.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          this.props.dispatch(actions.changeListGrouping(listName, options[i].value));
        }
      }
    }
  }
}
