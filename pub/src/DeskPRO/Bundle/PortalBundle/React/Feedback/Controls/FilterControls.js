import React from "react"
import _ from "lodash"
import TabRow from "./TabRow"
import TypeRow from "./TypeRow"

export default class FilterControls extends React.Component {
  updateFilter(filter) {
    this.props.updateFilter(filter);
  }
  setStatus(status_id) {
    let filter = this.props.filterModel;
    filter.status_categories = [];
    filter.setStatus(status_id);
    this.updateFilter(filter);
  }
  setStatusCategory(status_category_id) {
    let filter = this.props.filterModel;
    filter.toggleStatusCategory(status_category_id);
    this.updateFilter(filter);
  }

  setSort(new_sort) {
    let filter = this.props.filterModel;
    filter.changeSort(new_sort);
    this.updateFilter(filter);
  }
  toggleType(type_id) {
    let filter = this.props.filterModel;
    filter.toggleType(type_id);
    this.updateFilter(filter);
  }
  render() {
    return (
      <div className="feedback-filter">
        <TabRow
          available={this.props.available}
          filter={this.props.filterModel}
          setStatus={this.setStatus.bind(this)}
          setSort={this.setSort.bind(this)}
          setStatusCategory={this.setStatusCategory.bind(this)}
          />

        <TypeRow
          available={this.props.available.types}
          selected={this.props.filterModel.types}
          toggleType={this.toggleType.bind(this)}
          doSpin={this.props.doSpin}
          />
      </div>
    );
  }
}
