import React, { Component, PropTypes } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { LabelsForm } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/LabelsForm';

export class LabelsFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    matchMode: PropTypes.bool,
    activeItem: PropTypes.object,
    unsetParams: PropTypes.func.isRequired,
    filter: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, setParamsAction, stateValue, filter, unsetParams, activeItem, setActiveItem, matchMode } = this.props;
    const { label, icon, labels, param, modeParam } = filter;
    const selected = stateValue(param) || [];
    const mode = stateValue(modeParam);
    const isActive = Boolean(selected.length);

    const selectLabel = selectedLabel => {
      if (selected.indexOf(selectedLabel) === -1) {
        selected.push(selectedLabel);
        dispatch(setParamsAction({ [param]: selected, delayReload: true }));
      }
    };
    const deselectLabel = (deselectedLabel, event) => {
      event.preventDefault();
      if (selected.indexOf(deselectedLabel) !== -1) {
        selected.splice(selected.indexOf(deselectedLabel), 1);
        dispatch(setParamsAction({ [param]: selected, delayReload: true }));
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
                  resetFilter={unsetParams.bind(this, param)}>
        <Menu>
          <LabelsForm matchMode={matchMode}
                      params={{'get': () => mode}}
                      changeMode={newMode => dispatch(setParamsAction({[modeParam]: newMode, delayReload: true}))}
                      allLabels={labels}
                      selectedLabels={selected}
                      selectLabel={selectLabel}
                      deselectLabel={deselectLabel}/>
        </Menu>
      </FilterItem>
    );
  }

}