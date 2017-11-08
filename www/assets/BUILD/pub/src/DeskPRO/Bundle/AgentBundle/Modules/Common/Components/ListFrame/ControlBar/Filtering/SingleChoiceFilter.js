import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { SingleChoicePanelContainer } from '../../../Form/SingleChoicePanelContainer';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class SingleChoiceFilter extends Component {
  static propTypes = {
    stateValue:      PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    setActiveItem:   PropTypes.func,
    unsetParams:     PropTypes.func.isRequired,
    activeItem:      PropTypes.object,
    state:           PropTypes.object.isRequired,
    filter:          PropTypes.object.isRequired
  };

  reset = () => {
    const { filter, unsetParams } = this.props;
    const { param, options } = filter;
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
    unsetParams(params);
  };

  render() {
    const { setParamsAction, filter, activeItem, setActiveItem, unsetParams, state } = this.props;
    const { label, icon, param, quickFilter } = filter;
    const filterValue = state.get(param);
    const isActive    = Boolean(filterValue);

    return (
      <FilterItem
        activeItem={activeItem}
        selected={state.get(param) ? [state.get(param)] : []}
        setActiveItem={setActiveItem}
        icon={icon || 'filter'}
        label={label}
        isActive={isActive}
        resetFilter={this.reset}
      >
        <Menu>
          <SingleChoicePanelContainer
            title={label}
            depth
            currentParams={state}
            setParams={setParamsAction}
            resetSingleAction={unsetParams}
            quickFilter={quickFilter}
            item={filter}
          />
        </Menu>
      </FilterItem>
    );
  }
}
