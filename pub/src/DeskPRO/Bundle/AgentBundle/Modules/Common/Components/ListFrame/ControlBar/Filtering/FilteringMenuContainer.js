import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button } from '../Button';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Formsy from 'formsy-react';
import Moment from 'moment';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { DateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTimePicker';
import { LabelsFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/LabelsFilter';
import { ChoiceMenu, ChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import Immutable from 'immutable';

/**
 * This file is too big and contains several Filter* components, the reason why it's here and not
 * under Filtering/Filters folder in DateFilter/LabelsFilter/etc is that Menu and Item components are rendered
 * properly only if Menu is a first level child of Item and this condition gets broken when adding additional
 * DateFilter/LabelFilter/etc components.
 *
 * @todo Fix Menu component and split this into DateFilter/LabelFilter/SelectFilter
 */
@connect()
export class FilteringMenuContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filters: PropTypes.object.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {expanded: false};
  }

  toggleExpanded = () => this.setState({expanded: !this.state.expanded});
  collapse = () => this.setState({expanded: false});

  stateValue(param) {
    let value = this.props.state.get(param);
    if (Immutable.Iterable.isIterable(value)) {
      value = value.toJS();
    }

    return value;
  }

  getButtonLabel() {
    let label = '(none)';
    let count = 0;
    this.props.filters.map(filter => {
      const value = this.stateValue(filter.param);
      if ((value instanceof Array && value.length) || (!value instanceof Array)) {
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
    return (
      <li>
        <ClickOut
          onClickOut={this.collapse}
          onClick={this.toggleExpanded}
          ignoreNodes={[this.refs.menu, '.dpw-navigation-dropdown-panel', '.dpw-label-list']}>

          <Button
            ref="button"
            title="Filter by:"
            icon={null}
            label={this.getButtonLabel()}
          />
          <Positioned isOpen={this.state.expanded}
                      positionAt="left bottom"
                      positionTarget={this.refs.button}
                      ref="menu">
            <Menu>
              {this.props.filters.map((filter, index) => this.renderFilter(filter, index))}
            </Menu>
          </Positioned>
        </ClickOut>
      </li>
    );
  }

  // Generic <Filter /> component --------------------------------------------------------------------------------------

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
    const from = this.stateValue(fromParam);
    const to = this.stateValue(toParam);
    const isActive = Boolean(from || to);

    return (
      <FilterItem
        key={index}
        icon={icon || 'calendar-o'}
        label={label}
        isActive={isActive}
        resetFilter={() => this.unsetParams([fromParam, toParam])}
      >
        {this.renderDateCreatedItemContent(from, to)}
        <Menu>
          <div
            className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">

            <span className="dpw-navigation-dropdown-panel-close"><i className="fa fa-times"></i></span>

            <div className="dpw-date-picker">

              <div className="dpw-date-picker-panel-container">
                <Formsy.Form onValidSubmit={(model) => console.log('onValidSubmit', model)}>
                  <DateTimePicker
                    label="From"
                    name={fromParam}
                    className="dpw-date-picker-left"
                    initialValue={from}
                    />
                  <DateTimePicker
                    label="To"
                    name={toParam}
                    className="dpw-date-picker-right"
                    initialValue={to}
                    />

                  <div className=" dpw-date-picker-footer">
                    <button type="submit" className="dpw--panel-button">Apply Date Range Filter</button>
                  </div>
                </Formsy.Form>
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
    const { dispatch, setParamsAction } = this.props;
    const selected = this.stateValue(param) || [];
    const mode = this.stateValue(modeParam);
    const isActive = Boolean(selected.length);

    const selectLabel = (selectedLabel) => {
      if (selected.indexOf(selectedLabel) === -1) {
        selected.push(selectedLabel);
        dispatch(setParamsAction({[param]: selected}));
      }
    };
    const deselectLabel = (deselectedLabel) => {
      if (selected.indexOf(deselectedLabel) !== -1) {
        selected.splice(selected.indexOf(deselectedLabel), 1);
        dispatch(setParamsAction({[param]: selected}));
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
            changeMode={(newMode) => dispatch(setParamsAction({[modeParam]: newMode}))}
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

  renderSelectFilter({ label, icon, param, multiple, options }, index) {
    const { dispatch, setParamsAction } = this.props;
    const filterValue = this.stateValue(param) || [];
    const isActive = Boolean(filterValue.length);

    // onClick depending on if filter selects multiple values or a single value
    let onClick;
    if (multiple === false) {
      onClick = (value) => () => dispatch(setParamsAction({[param]: value}));
    } else {
      onClick = (value) => () => {
        if (filterValue.indexOf(value) === -1) {
          filterValue.push(value);
        } else {
          filterValue.splice(filterValue.indexOf(value), 1);
        }
        dispatch(setParamsAction({[param]: filterValue}));
      };
    }

    const renderNested = (nested) => {
      if (!nested || !nested.length) {
        return <span />;
      }

      return (
        <ul>
          {nested.map((option, i) =>
              <ChoiceMenuOption
                key={i}
                value={option.value}
                values={filterValue}
                label={option.label}
                onClick={onClick(option.value)}
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
          <ChoiceMenu title={label}>
            <ul>
              {options.map((option, i) =>
                <ChoiceMenuOption
                  key={i}
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
}
