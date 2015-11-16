import React, { Component, PropTypes } from 'react';
import { CheckboxContainer } from './MassAction/CheckboxContainer';
import { SortingMenu } from './Sorting/SortingMenu';
import { FilteringMenuContainer } from './Filtering/FilteringMenuContainer';
import { ViewMenuContainer } from './View/ViewMenuContainer';
import { ListFrameMenu } from '../../ListFrameMenu';

export class ControlBar extends Component {

  static propTypes = {
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    }),
    sorting: PropTypes.shape({
      options: PropTypes.objectOf(PropTypes.shape({
        label: PropTypes.string.isRequired,
        icon: PropTypes.string.isRequired
      })),
      sort: PropTypes.string.isRequired,
      order: PropTypes.string.isRequired,
      sortAction: PropTypes.func.isRequired,
      orderAction: PropTypes.func.isRequired
    }),
    filtering: PropTypes.shape({
      filters: PropTypes.arrayOf(PropTypes.oneOfType([
        PropTypes.shape({
          type: PropTypes.oneOf(['date']).isRequired,
          label: PropTypes.string.isRequired,
          fromParam: PropTypes.string.isRequired,
          toParam: PropTypes.string.isRequired
        }),
        PropTypes.shape({
          type: PropTypes.oneOf(['labels']).isRequired,
          label: PropTypes.string.isRequired,
          param: PropTypes.string.isRequired,
          modeParam: PropTypes.string.isRequired,
          labels: PropTypes.array.isRequired
        }),
        PropTypes.shape({
          type: PropTypes.oneOf(['select']).isRequired,
          label: PropTypes.string.isRequired,
          param: PropTypes.string.isRequired,
          options: PropTypes.arrayOf(PropTypes.shape({
            label: PropTypes.string.isRequired,
            value: PropTypes.any.isRequired,
            nested: PropTypes.array
          }))
        })
      ])),
      setParamsAction: PropTypes.func.isRequired,
      state: PropTypes.object.isRequired
    }),
    view: PropTypes.shape({
      options: PropTypes.objectOf(PropTypes.shape({
        label: PropTypes.string.isRequired,
        icon: PropTypes.string.isRequired,
        configurableFields: PropTypes.object.isRequired,
        visibleFields: PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
        toggleFieldVisibility: PropTypes.func.isRequired
      })).isRequired,
      viewMode: PropTypes.string.isRequired,
      viewModeAction: PropTypes.func.isRequired
    })
  };

  render() {
    const { checkbox, sorting, filtering, view } = this.props;
    console.log(filtering);

    return (
      <ListFrameMenu>
        <CheckboxContainer {...checkbox} />
        <SortingMenu {...sorting} />
        <li>
          <hr/>
        </li>
        <FilteringMenuContainer {...filtering} />
        <li>
          <hr/>
        </li>
        <ViewMenuContainer {...view} />
      </ListFrameMenu>
    );
  }
}

