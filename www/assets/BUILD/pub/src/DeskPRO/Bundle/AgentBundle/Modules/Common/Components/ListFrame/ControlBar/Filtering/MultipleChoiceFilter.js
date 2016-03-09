import React, { Component, PropTypes } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { ChoiceMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { CheckboxOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/CheckboxOption';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class MultipleChoiceFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
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
    const { dispatch, setParamsAction, stateValue, filter, activeItem, unsetParams } = this.props;
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
    let filterValues = this.props.state.params[param];

    // onClick depending on if filter selects multiple values or a single value
    let onClick;
    if (multiple === false) {
      onClick = (value) => () => dispatch(setParamsAction({ [param]: value, delayReload: true }));
    } else {
      onClick = (value, newParam = null) => () => {
        let filterValues = newParam ? this.props.state.params[newParam] : filterValues;
        filterValues = filterValues ? filterValues : [];
        console.log('Value', value);
        if (filterValues.indexOf(value) === -1) {
          filterValues.push(value);
        } else {
          filterValues.splice(filterValues.indexOf(value), 1);
        }
        console.log('Filter values', filterValues);
        this.props.setParam({ param: [newParam ? newParam : param], value: filterValues });
        //dispatch(setParamsAction({ [newParam ? newParam : param]: filterValues, delayReload: true }));
      };
    }
    const isActive = filterValues? Boolean(filterValues.length) : false;

    console.log('Param', param);
    console.log('State in multiple choice', this.props.state);
    return (
      <FilterItem activeItem={activeItem}
                  selected={this.getSelected(options, filterValue)}
                  icon={icon || 'filter'}
                  label={label}
                  isActive={isActive}
                  resetFilter={unsetParams.bind(this, params)}>
        <Menu>
          <ChoiceMenu title={label} quickFilter={quickFilter} submenu>
            <ul>
              {options.map((option, index) =>
                  <CheckboxOption key={index}
                                  value={option.value}
                                  values={this.props.state.params[param]}
                                  label={option.label}
                                  onClick={onClick(option.value)}>
                    {
                      option.nested && option.nested.length > 0
                      && <NestedMultipleChoice nested={option.nested}
                                               filterValue={this.props.state.params}
                                               onClick={onClick}/>
                    }
                  </CheckboxOption>
              )}
            </ul>
          </ChoiceMenu>
        </Menu>
      </FilterItem>
    );
  }

}

export class NestedMultipleChoice extends Component {
  static propTypes = {
    nested: PropTypes.array.isRequired,
    filterValue: PropTypes.array.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const {nested, filterValue, onClick} = this.props;
    return (
      <ul>
        {nested.map((option, index) =>
            <CheckboxOption key={index}
                            value={option.value}
                            values={filterValue[option.param]}
                            label={option.label}
                            onClick={onClick(option.value, option.param)}/>
        )}
      </ul>
    );
  }
}