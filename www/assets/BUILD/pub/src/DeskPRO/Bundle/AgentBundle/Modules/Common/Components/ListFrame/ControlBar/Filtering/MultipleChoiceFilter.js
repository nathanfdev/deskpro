import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { ChoiceMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { CheckboxOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/CheckboxOption';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class MultipleChoiceFilter extends Component {
  static propTypes = {
    setParam:      PropTypes.func.isRequired,
    unsetParam:    PropTypes.func.isRequired,
    activeItem:    PropTypes.object,
    currentParams: PropTypes.object.isRequired,
    filter:        PropTypes.object.isRequired
  };

  getSelected(options, filterValue) {
    const flatOptions = [...options];
    options.forEach(opt => {
      if (opt.nested) {
        flatOptions.push(...opt.nested);
      }
    });
    const value    = filterValue instanceof Array ? filterValue : [filterValue];
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

  reset = () => {
    const { filter, unsetParam } = this.props;
    unsetParam(filter.param);
  };

  render() {
    const { setParam, filter, activeItem, currentParams } = this.props;
    const { label, icon, param, quickFilter, options } = filter;
    const params = [param];
    options.map(option => {
      if (option.hasOwnProperty('nested')) {
        option.nested.map(opt => {
          params.push(opt.param);
          return null;
        });
      }
      return null;
    });
    let filterValues = currentParams[param];

    const onClick  = (value, newParam = null) => () => {
      filterValues = newParam ? currentParams[newParam] : filterValues;
      filterValues = filterValues ? filterValues : [];
      if (filterValues.indexOf(value) === -1) {
        filterValues.push(value);
      } else {
        filterValues.splice(filterValues.indexOf(value), 1);
      }
      setParam({ param: [newParam ? newParam : param], value: filterValues });
    };
    const isActive = filterValues ? Boolean(filterValues.length) : false;

    return (
      <FilterItem
        activeItem={activeItem}
        selected={this.getSelected(options, filterValues)}
        icon={icon || 'filter'}
        label={label}
        isActive={isActive}
        resetFilter={this.reset}
      >
        <Menu>
          <ChoiceMenu title={label} quickFilter={quickFilter} submenu>
            <ul>
              {options.map((option, index) =>
                             <CheckboxOption
                               key={index}
                               value={option.value}
                               values={currentParams[param]}
                               label={option.label}
                               onClick={onClick(option.value)}
                             >
                               {
                                 option.nested && option.nested.length > 0
                                 && <NestedMultipleChoice
                                   nested={option.nested}
                                   filterValue={currentParams}
                                   onClick={onClick}
                                 />
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
    nested:      PropTypes.array.isRequired,
    filterValue: PropTypes.object.isRequired,
    onClick:     PropTypes.func.isRequired
  };

  render() {
    const { nested, filterValue, onClick } = this.props;
    return (
      <ul>
        {nested.map((option, index) =>
                      <CheckboxOption
                        key={index}
                        value={option.value}
                        values={filterValue[option.param]}
                        label={option.label}
                        onClick={onClick(option.value, option.param)}
                      />
        )}
      </ul>
    );
  }
}
