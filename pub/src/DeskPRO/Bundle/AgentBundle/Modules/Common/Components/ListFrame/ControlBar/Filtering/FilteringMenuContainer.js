import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button } from '../Button';
import Positioned from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Moment from 'moment';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { DateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/DateTimePicker';
import { LabelsFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/LabelsFilter';
import { ChoiceMenu, ChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import Immutable from 'immutable';

@connect()
export class FilteringMenuContainer extends Component {
  static propTypes = {
    onMenuUnmount: PropTypes.func,
    dispatch: PropTypes.func.isRequired,
    filters: PropTypes.array.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  toggleExpanded = () => this.setState({ expanded: !this.state.expanded });
  collapse = () => this.setState({ expanded: false });

  stateValue(param) {
    if (param instanceof Array) {
      const result = [];
      param.map(item => {
        let value = this.props.state.get(item);
        if (Immutable.Iterable.isIterable(value)) {
          value = value.toJS();
          value.map(item1=> {
            result.push(item1);
          });
        }
      });
      return [...new Set(result)];
    }
    let value = this.props.state.get(param);
    if (Immutable.Iterable.isIterable(value)) {
      value = value.toJS();
    }
    return value;
  }

  getButtonLabel() {
    const { filters = [] } = this.props;
    let label = '(none)';
    let count = 0;
    let value;
    filters.map(filter => {
      if (filter.hasOwnProperty('param')) {
        value = this.stateValue(filter.param);
      } else if (filter.hasOwnProperty('fromParam')) {
        value = this.stateValue(filter.fromParam);
        if (!value) {
          value = this.stateValue(filter.toParam);
        }
      }
      if (value && ((value instanceof Array && value.length) || !(value instanceof Array))) {
        label = filter.label;
        count++;
      }
    });

    if (count > 1) {
      label = count + ' Options';
    }

    return label;
  }

  render() {
    const { dispatch, state, setParamsAction, onMenuUnmount, filters = [] } = this.props;

    return (
      <li ref="menuItem">
        <Button
          isActive={this.state.expanded}
          ref="button"
          title="Filter by:"
          icon={null}
          label={this.getButtonLabel()}
          onClick={this.toggleExpanded}
          />
        <Positioned isOpen={this.state.expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.button}>
          <ClickOut
            onClickOut={this.collapse}
            ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel', '.dpw-label-list']}
            additionalNodes={['.dpw-navigation-dropdown-item-clear']}>
            <FilteringMenu
              dispatch={dispatch}
              filters={filters}
              state={state}
              stateValue={this.stateValue.bind(this)}
              onMenuUnmount={onMenuUnmount}
              setParamsAction={setParamsAction}
              />
          </ClickOut>
        </Positioned>
      </li>
    );
  }
}

/**
 * This class is too big and contains several Filter* components, the reason why it's here and not
 * under Filtering/Filters folder in DateFilter/LabelsFilter/etc is that Menu and Item components are rendered
 * properly only if Menu is a first level child of Item and this condition gets broken when adding additional
 * DateFilter/LabelFilter/etc components.
 *
 * @todo Fix Menu component and split this into DateFilter/LabelFilter/SelectFilter
 */
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

  // Date filter -------------------------------------------------------------------------------------------------------

  renderDateCreatedItemContent(from, to) {
    if (from || to) {
      return (
        <span className="dpw-navigation-dropdown-item-inline-info">
          {from ? Moment(from).format('DD/MM/YYYY') : '...'} - {to ? Moment(to).format('DD/MM/YYYY') : '...'}
        </span>
      );
    }
  }

  renderDateFilter({ label, icon, fromParam, toParam }, index) {
    const { dispatch, setParamsAction, stateValue } = this.props;
    const from = stateValue(fromParam);
    const to = stateValue(toParam);
    const isActive = Boolean(from || to);

    return (
      <FilterItem
        key={index}
        icon={icon || 'calendar-o'}
        label={label}
        isActive={isActive}
        resetFilter={() => this.unsetParams([fromParam, toParam])}>

        {this.renderDateCreatedItemContent(from, to)}
        <Menu>
          <div
            className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">
            <div className="dpw-date-picker">

              <div className="dpw-date-picker-panel-container">
                <form>
                  <DateTimePicker
                    label="From"
                    className="dpw-date-picker-left"
                    value={from}
                    onChange={value => dispatch(setParamsAction({[fromParam]: value, delayReload: true}))}/>
                  <DateTimePicker
                    label="To"
                    className="dpw-date-picker-right"
                    value={to}
                    onChange={value => dispatch(setParamsAction({[toParam]: value, delayReload: true}))}/>
                </form>
              </div>
            </div>
          </div>
        </Menu>
      </FilterItem>
    );
  }


  // Labels filter -----------------------------------------------------------------------------------------------------

  renderLabelsFilterInfo(labels) {
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

  renderLabelsFilter({ label, icon, labels, param, modeParam }, index) {
    const { dispatch, setParamsAction, stateValue } = this.props;
    const selected = stateValue(param) || [];
    const mode = stateValue(modeParam);
    const isActive = Boolean(selected.length);

    const selectLabel = selectedLabel => {
      if (selected.indexOf(selectedLabel) === -1) {
        selected.push(selectedLabel);
        dispatch(setParamsAction({ [param]: selected, delayReload: true }));
      }
    };
    const deselectLabel = deselectedLabel => {
      if (selected.indexOf(deselectedLabel) !== -1) {
        selected.splice(selected.indexOf(deselectedLabel), 1);
        dispatch(setParamsAction({ [param]: selected, delayReload: true }));
      }
    };

    return (
      <FilterItem
        key={index}
        icon={icon || 'tags'}
        label={label}
        isActive={isActive}
        resetFilter={() => this.unsetParams(param)}
        >
        {this.renderLabelsFilterInfo(selected)}
        <Menu>
          <LabelsFilter
            params={{'get': () => mode}}
            changeMode={newMode => dispatch(setParamsAction({[modeParam]: newMode, delayReload: true}))}
            allLabels={labels}
            selectedLabels={selected}
            selectLabel={selectLabel}
            deselectLabel={deselectLabel}
            />
        </Menu>
      </FilterItem>
    );
  }

  // Select filter -----------------------------------------------------------------------------------------------------

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
    return this.renderLabelsFilterInfo(selected);
  }

  renderSelectFilter({ label, icon, param, multiple, quickFilter, options }, index) {
    const { dispatch, setParamsAction, stateValue } = this.props;
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

    const renderNested = (nested) => {
      if (!nested || !nested.length) {
        return <span />;
      }

      return (
        <ul>
          {nested.map((option, index1) =>
              <ChoiceMenuOption
                key={index1}
                value={option.value}
                values={filterValue}
                label={option.label}
                onClick={onClick(option.value, option.param)}
                />
          )}
        </ul>
      );
    };
    return (
      <FilterItem
        key={index}
        icon={icon || 'filter'}
        label={label}
        isActive={isActive}
        resetFilter={() => this.unsetParams(param)}
        >
        {this.renderSelectFilterInfo(options, filterValue)}
        <Menu>
          <ChoiceMenu title={label} quickFilter={quickFilter}>
            <ul>
              {options.map((option, index2) =>
                  <ChoiceMenuOption
                    key={index2}
                    value={option.value}
                    values={filterValue}
                    label={option.label}
                    onClick={onClick(option.value)}
                    >
                    {renderNested(option.nested)}
                  </ChoiceMenuOption>
              )}
            </ul>
          </ChoiceMenu>
        </Menu>
      </FilterItem>
    );
  }

  renderFilter(filter, index) {
    switch (filter.type) {
      case 'date':
        return this.renderDateFilter(filter, index);
      case 'labels':
        return this.renderLabelsFilter(filter, index);
      case 'select':
        return this.renderSelectFilter(filter, index);
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