import React, { Component, PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { LabelsForm } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/LabelsForm';

export class LabelsFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    renderFilterInfo: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    activeItem: PropTypes.object,
    unsetParams: PropTypes.func.isRequired,
    filter: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, setParamsAction, stateValue, filter, renderFilterInfo, unsetParams, activeItem, setActiveItem } = this.props;
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
    const deselectLabel = deselectedLabel => {
      if (selected.indexOf(deselectedLabel) !== -1) {
        selected.splice(selected.indexOf(deselectedLabel), 1);
        dispatch(setParamsAction({ [param]: selected, delayReload: true }));
      }
    };

    return (
      <FilterItem activeItem={activeItem}
                  icon={icon || 'tags'}
                  label={label}
                  isActive={isActive}
                  setActiveItem={setActiveItem}
                  resetFilter={unsetParams.bind(this, param)}>
        {renderFilterInfo(selected)}
        <Menu>
          <LabelsForm params={{'get': () => mode}}
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