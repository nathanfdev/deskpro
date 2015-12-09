import React, { Component, PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { ChoiceMenu, ChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class MultipleChoiceFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    unsetParams: PropTypes.func.isRequired,
    renderLabelsFilterInfo: PropTypes.func.isRequired,
    activeItem: PropTypes.object,
    state: PropTypes.object.isRequired,
    filter: PropTypes.object.isRequired
  };

  renderSelectFilterInfo(options, filterValue) {
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
    return this.props.renderLabelsFilterInfo(selected);
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
                  setActiveItem={setActiveItem}
                  icon={icon || 'filter'}
                  label={label}
                  isActive={isActive}
                  resetFilter={unsetParams.bind(this, params)}
        >
        {this.renderSelectFilterInfo(options, filterValue)}
        <Menu>
          <ChoiceMenu title={label} quickFilter={quickFilter}>
            <ul>
              {options.map((option, index) =>
                  <ChoiceMenuOption key={index}
                                    value={option.value}
                                    values={filterValue}
                                    label={option.label}
                                    onClick={onClick(option.value)}>
                    {
                      option.nested && option.nested.length > 0
                      && <NestedMultipleChoice nested={option.nested}
                                               filterValue={filterValue}
                                               onClick={onClick}/>
                    }
                  </ChoiceMenuOption>
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
            <ChoiceMenuOption key={index}
                              value={option.value}
                              values={filterValue}
                              label={option.label}
                              onClick={onClick(option.value, option.param)}/>
        )}
      </ul>
    );
  }
}