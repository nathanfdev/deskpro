import React, { PropTypes } from 'react';
import TabRow from './TabRow';
import TypeRow from './TypeRow';

export default class FilterControls extends React.Component {

  static propTypes = {
    available: PropTypes.object,
    updateFilter: PropTypes.func,
    filterModel: PropTypes.object,
    doSpin: PropTypes.any
  };

  setStatus(statusId) {
    const { filterModel } = this.props;
    filterModel.status_categories = [];
    filterModel.setStatus(statusId);

    this.updateFilter(filterModel);
  }

  setStatusCategory(statusCategoryId) {
    const { filterModel } = this.props;
    filterModel.toggleStatusCategory(statusCategoryId);

    this.updateFilter(filterModel);
  }

  setSort(newSort) {
    const { filterModel } = this.props;
    filterModel.changeSort(newSort);

    this.updateFilter(filterModel);
  }

  updateFilter(filter) {
    this.props.updateFilter(filter);
  }

  toggleType(typeId) {
    const { filterModel } = this.props;
    filterModel.toggleType(typeId);

    this.updateFilter(filterModel);
  }

  render() {
    const { available, filterModel, doSpin } = this.props;

    return (
      <div className="feedback-filter">
        <TabRow
          available={available}
          filter={filterModel}
          setStatus={this.setStatus.bind(this)}
          setSort={this.setSort.bind(this)}
          setStatusCategory={this.setStatusCategory.bind(this)}
          />

        <TypeRow
          available={available.types}
          selected={filterModel.types}
          toggleType={this.toggleType.bind(this)}
          doSpin={doSpin}
          />
      </div>
    );
  }
}
