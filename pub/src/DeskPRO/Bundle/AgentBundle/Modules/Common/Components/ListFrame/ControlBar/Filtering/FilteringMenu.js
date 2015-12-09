import React, { Component, PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { LabelsFilter } from './LabelsFilter';
import { DateFilter } from './DateFilter';
import { MultipleChoiceFilter } from './MultipleChoiceFilter';

export class FilteringMenu extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    onMenuUnmount: PropTypes.func,
    filters: PropTypes.array.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired
  };

  componentWillUnmount() {
    const {dispatch, onMenuUnmount} = this.props;
    if (onMenuUnmount) {
      dispatch(onMenuUnmount());
    }
  }

  // Generic <Filter /> component --------------------------------------------------------------------------------------
  unsetParams(params) {
    const unset = {};
    (params instanceof Array ? params : [params]).forEach(param => unset[param] = undefined);
    this.props.dispatch(this.props.setParamsAction(unset));
  }

  renderFilterInfo(labels) {
    if (labels.length) {
      const result = [<span className="dpw-navigation-dropdown-item-inline-info">{labels[0]}</span>];
      if (labels.length > 1) {
        result.push(
          <span className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{labels.length - 1}
          </span>
        );
      }

      return result;
    }

    return <span />;
  }

  renderFilter(filter, index) {
    switch (filter.type) {
      case 'date':
        return (
          <DateFilter {...this.props} filter={filter}
                                      key={index}
                                      unsetParams={this.unsetParams}/>
        );
      case 'labels':
        return (
          <LabelsFilter {...this.props} filter={filter}
                                        key={index}
                                        unsetParams={this.unsetParams}
                                        renderFilterInfo={this.renderFilterInfo}/>
        );
      case 'select':
        return (
          <MultipleChoiceFilter {...this.props} filter={filter}
                                                key={index}
                                                unsetParams={this.unsetParams}
                                                renderFilterInfo={this.renderFilterInfo}/>
        );
      default:
        throw new Error(`Unknown filter type - ${filter.type}`);
    }
  }

  render() {
    const { filters = [] } = this.props;

    return (
      <Menu>
        {filters.map((filter, index) => this.renderFilter(filter, index))}
      </Menu>
    );
  }
}