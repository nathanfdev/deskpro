import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/publishNavActions'
import { Nav } from './Nav';

@connect(state => state.PublishNav)
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadCounts('articles', this.props.articles.grouped_by));
    this.props.dispatch(actions.loadCounts('news', this.props.articles.grouped_by));
    this.props.dispatch(actions.loadCounts('downloads', this.props.articles.grouped_by));
    this.props.dispatch(actions.loadCategories());
  }

  render() {
    const onGroupingChange = (listName) => this.onGroupingChange(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);

    return (
      <Nav
        articles={this.props.articles}
        news={this.props.news}
        downloads={this.props.downloads}
        categories={this.props.categories}
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
