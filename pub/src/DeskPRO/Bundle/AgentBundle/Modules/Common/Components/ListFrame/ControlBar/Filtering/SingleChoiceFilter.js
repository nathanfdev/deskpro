import React, { Component, PropTypes } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { SingleChoicePanel } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/SingleChoicePanel';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class SingleChoiceFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    unsetParams: PropTypes.func.isRequired,
    activeItem: PropTypes.object,
    state: PropTypes.object.isRequired,
    filter: PropTypes.object.isRequired
  };

  getSelected(options, filterValue) {
    const flatOptions = [...options];
    options.forEach(opt => {
      if (opt.nested) {
        flatOptions.push(...opt.nested);
      }
    });
    const value = filterValue instanceof Array ? filterValue : [filterValue];
    const selected = [];
    value.forEach(val => {
      flatOptions.forEach(opt => {
        if (opt.value === val) {
          selected.push(opt.label);
        }
      });
    });
    return selected;
  }

  render() {
    const { dispatch, setParamsAction, stateValue, filter, activeItem, setActiveItem, unsetParams } = this.props;
    const { label, icon, param, multiple, quickFilter, options } = filter;
    const params = [param];
    options.map(option=> {
      if (option.hasOwnProperty('nested')) {
        option.nested.map(opt => {
          params.push(opt.param);
        });
      }
    });
    const filterValue = stateValue([...new Set(params)]) || [];
    const isActive = Boolean(filterValue.length);

    // onClick depending on if filter selects multiple values or a single value
    let onClick;
    if (multiple === false) {
      onClick = (value) => () => dispatch(setParamsAction({ [param]: value, delayReload: true }));
    } else {
      onClick = (value, newParam = null) => () => {
        let filterValues = newParam ? this.props.state.get(newParam) : this.props.state.get(param);
        if (filterValues) {
          filterValues = filterValues.toJS();
        } else {
          filterValues = [];
        }
        if (filterValues.indexOf(value) === -1) {
          filterValues.push(value);
        } else {
          filterValues.splice(filterValues.indexOf(value), 1);
        }
        dispatch(setParamsAction({ [newParam ? newParam : param]: filterValues, delayReload: true }));
      };
    }

    return (
      <FilterItem activeItem={activeItem}
                  selected={this.getSelected(options, filterValue)}
                  setActiveItem={setActiveItem}
                  icon={icon || 'filter'}
                  label={label}
                  isActive={isActive}
                  resetFilter={unsetParams.bind(this, params)}>
        <Menu>
          <SingleChoicePanel title={label}
                             depth
                             quickFilter={quickFilter}
                             item={filter}/>
        </Menu>
      </FilterItem>
    );
  }
}
