import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { LabelsFilter } from './LabelsFilter';
import { DateFilter } from './DateFilter';
import { DatePeriodFilter } from './DatePeriodFilter';
import { MultipleChoiceFilter } from './MultipleChoiceFilter';
import { SingleChoiceFilter } from './SingleChoiceFilter';

export class FilteringMenu extends Component {
  static propTypes = {
    onMenuUnmount: PropTypes.func,
    filters:       PropTypes.array.isRequired,
    setParam:      PropTypes.func.isRequired,
    unsetParam:    PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired
  };

  componentWillUnmount = () => {
    this.props.onMenuUnmount();
  };

  // Generic <Filter /> component --------------------------------------------------------------------------------------

  renderFilter = (filter, index) => {
    switch (filter.type) {
      case 'date':
        return (<DateFilter {...this.props} filter={filter} key={index} />);
      case 'datePeriod':
        return (<DatePeriodFilter {...this.props} filter={filter} key={index} />);
      case 'labels':
        return (<LabelsFilter {...this.props} filter={filter} key={index} matchMode />);
      case 'select':
        return (<MultipleChoiceFilter {...this.props} filter={filter} key={index} />);
      case 'singleSelect':
        return (<SingleChoiceFilter {...this.props} filter={filter} key={index} />);
      default:
        throw new Error(`Unknown filter type - ${filter.type}`);
    }
  };

  render = () => {
    const { filters = [] } = this.props;

    return (
      <Menu>
        {filters.map((filter, index) => this.renderFilter(filter, index))}
      </Menu>
    );
  }
}
