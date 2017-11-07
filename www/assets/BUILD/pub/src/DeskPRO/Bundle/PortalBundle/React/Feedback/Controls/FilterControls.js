import PropTypes from 'prop-types';
import React from 'react';
import { TabRow } from './TabRow';
import { TypeRow } from './TypeRow';

export class FilterControls extends React.Component {

  static propTypes = {
    available:    PropTypes.object,
    updateFilter: PropTypes.func,
    filterModel:  PropTypes.object,
    doSpin:       PropTypes.any
  };

  onToggleType = typeId => {
    const { filterModel } = this.props;
    filterModel.toggleType(typeId);

    this.updateFilter(filterModel);
  };

  onSetStatusCategory = statusCategoryId => {
    const { filterModel } = this.props;
    filterModel.toggleStatusCategory(statusCategoryId);

    this.updateFilter(filterModel);
  };

  onSetStatus = statusId => {
    const { filterModel } = this.props;
    filterModel.status_categories = [];
    filterModel.setStatus(statusId);

    this.updateFilter(filterModel);
  };

  onSetSort = newSort => {
    const { filterModel } = this.props;
    filterModel.changeSort(newSort);

    this.updateFilter(filterModel);
  };

  updateFilter(filter) {
    this.props.updateFilter(filter);
  }

  render() {
    const { available, filterModel, doSpin } = this.props;

    return (
      <div className="feedback-filter">
        <TabRow
          available={available}
          filter={filterModel}
          setStatus={this.onSetStatus}
          setSort={this.onSetSort}
          setStatusCategory={this.onSetStatusCategory}
        />
        <TypeRow
          available={available.types}
          selected={filterModel.types}
          toggleType={this.onToggleType}
          doSpin={doSpin}
        />
      </div>
    );
  }
}
