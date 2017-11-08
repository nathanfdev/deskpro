import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Menu } from '../../../Menu/Menu';
import { FilterItem } from '../../../Menu/FilterItem';
import { LabelsForm } from '../../../Form/LabelsForm';

export class LabelsFilter extends Component {
  static propTypes = {
    setParam:      PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    matchMode:     PropTypes.bool,
    activeItem:    PropTypes.object,
    unsetParam:    PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired,
    filter:        PropTypes.object.isRequired
  };

  reset = () => {
    const { filter, unsetParam } = this.props;
    unsetParam(filter.param);
  };

  selectLabel = (selectedLabel, event) => {
    event.preventDefault();
    const { setParam, currentParams } = this.props;
    const { param } = this.props.filter;
    const selected = currentParams[param] || [];
    if (selected.indexOf(selectedLabel) === -1) {
      selected.push(selectedLabel);
      setParam({ param: [param], value: selected });
    }
  };

  deselectLabel = (deselectedLabel, event) => {
    event.preventDefault();
    const { setParam, unsetParam, currentParams } = this.props;
    const { param } = this.props.filter;
    const selected = currentParams[param] || [];
    if (selected.indexOf(deselectedLabel) > -1) {
      selected.splice(selected.indexOf(deselectedLabel), 1);
    }
    if (selected.length > 0) {
      setParam({ param: [param], value: selected });
    } else {
      unsetParam(param);
    }
  };

  render() {
    const { setParam, currentParams, filter, activeItem, setActiveItem, matchMode } = this.props;
    const { label, icon, labels, param, modeParam } = filter;
    const selected = currentParams[param] || [];
    const mode     = currentParams[modeParam];
    const isActive = Boolean(selected.length);

    return (
      <FilterItem
        activeItem={activeItem}
        filterType={param}
        icon={icon || 'tags'}
        label={label}
        isActive={isActive}
        setActiveItem={setActiveItem}
        selected={selected}
        resetFilter={this.reset}
      >
        <Menu>
          <LabelsForm
            matchMode={matchMode}
            params={{ get: () => mode }}
            changeMode={newMode => setParam({ param: [modeParam], value: newMode })}
            allLabels={labels}
            selectedLabels={selected}
            selectLabel={this.selectLabel}
            deselectLabel={this.deselectLabel}
          />
        </Menu>
      </FilterItem>
    );
  }

}
