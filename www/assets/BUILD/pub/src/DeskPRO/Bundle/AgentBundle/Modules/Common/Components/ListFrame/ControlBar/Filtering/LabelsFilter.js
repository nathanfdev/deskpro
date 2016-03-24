import React, { Component, PropTypes } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { LabelsForm } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/LabelsForm';

export class LabelsFilter extends Component {
  static propTypes = {
    setParam: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    matchMode: PropTypes.bool,
    activeItem: PropTypes.object,
    unsetParam: PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired,
    filter: PropTypes.object.isRequired
  };

  render() {
    const { setParam, currentParams, filter, unsetParam, activeItem, setActiveItem, matchMode } = this.props;
    const { label, icon, labels, param, modeParam } = filter;
    const selected = currentParams[param] || [];
    const mode = currentParams[modeParam];
    const isActive = Boolean(selected.length);

    const selectLabel = (selectedLabel, event) => {
      event.preventDefault();
      if (selected.indexOf(selectedLabel) === -1) {
        selected.push(selectedLabel);
        setParam({ param: [param], value: selected });
      }
    };
    const deselectLabel = (deselectedLabel, event) => {
      event.preventDefault();
      if (selected.indexOf(deselectedLabel) > -1) {
        selected.splice(selected.indexOf(deselectedLabel), 1);
      }
      if (selected.length > 0) {
        setParam({ param: [param], value: selected });
      } else {
        unsetParam(param);
      }
    };

    return (
      <FilterItem activeItem={activeItem}
                  filterType={param}
                  icon={icon || 'tags'}
                  label={label}
                  isActive={isActive}
                  setActiveItem={setActiveItem}
                  selected={selected}
                  resetFilter={unsetParam.bind(this, param)}>
        <Menu>
          <LabelsForm matchMode={matchMode}
                      params={{'get': () => mode}}
                      changeMode={newMode => setParam({param: [modeParam], value: newMode})}
                      allLabels={labels}
                      selectedLabels={selected}
                      selectLabel={selectLabel}
                      deselectLabel={deselectLabel}/>
        </Menu>
      </FilterItem>
    );
  }

}