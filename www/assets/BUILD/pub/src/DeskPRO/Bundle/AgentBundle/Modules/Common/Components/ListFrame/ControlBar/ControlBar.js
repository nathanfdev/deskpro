import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { SortingMenu } from './Sorting/SortingMenu';
import { FilteringMenuContainer } from './Filtering/FilteringMenuContainer';
import { ViewMenuContainer } from './View/ViewMenuContainer';


export class ControlBar extends Component {

  static propTypes = {
    applyParams:   PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired,
    sorting:       PropTypes.objectOf(
      PropTypes.shape({
        label: PropTypes.string.isRequired,
        icon:  PropTypes.string.isRequired
      })
    ),
    filters: PropTypes.arrayOf(PropTypes.oneOfType([
      PropTypes.shape({
        type:      PropTypes.oneOf(['date']).isRequired,
        label:     PropTypes.string.isRequired,
        fromParam: PropTypes.string.isRequired,
        toParam:   PropTypes.string.isRequired
      }),
      PropTypes.shape({
        type:      PropTypes.oneOf(['labels']).isRequired,
        label:     PropTypes.string.isRequired,
        param:     PropTypes.string.isRequired,
        modeParam: PropTypes.string.isRequired,
        labels:    PropTypes.object.isRequired
      }),
      PropTypes.shape({
        type:    PropTypes.oneOf(['select']).isRequired,
        label:   PropTypes.string.isRequired,
        param:   PropTypes.string.isRequired,
        options: PropTypes.arrayOf(PropTypes.shape({
          label:  PropTypes.string.isRequired,
          value:  PropTypes.any.isRequired,
          nested: PropTypes.array
        }))
      })
    ])),
    view: PropTypes.shape({
      options: PropTypes.objectOf(PropTypes.shape({
        label:  PropTypes.string.isRequired,
        icon:   PropTypes.string.isRequired,
        fields: PropTypes.object.isRequired
      })).isRequired,
      viewMode:                PropTypes.string.isRequired,
      viewModeAction:          PropTypes.func.isRequired,
      onViewFieldsMenuUnmount: PropTypes.func,
      toggleFieldVisibility:   PropTypes.func.isRequired,
      changeFieldOrder:        PropTypes.func.isRequired
    })
  };

  componentWillMount() {
    this.setState({
      changed: false,
      params:  this.props.currentParams.toJS()
    });
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      params: nextProps.currentParams.toJS()
    });
  }

  setParam = (params) => {
    const newParams = this.state.params;
    newParams[params.param] = params.value;
    this.setState({
      changed: true,
      params:  newParams
    });
  };

  unsetParam = (param) => {
    const newParams = this.state.params;
    if (Array.isArray(param)) {
      param.forEach((item) => {
        newParams[item] = undefined;
      });
    } else {
      newParams[param] = undefined;
    }
    this.setState({
      changed: true,
      params:  newParams
    });
  };

  reloadList = () => {
    const { applyParams } = this.props;
    if (this.state.changed) {
      applyParams(this.state.params);
    }
    this.setState({
      changed: false
    });
  };

  render() {
    const { sorting, filters, view } = this.props;

    return (
      <ul className="dpwd-navigation-dropdown-top-row-main-list">
        {sorting &&
        <SortingMenu options={sorting}
          currentParams={this.state.params}
          setParam={this.setParam}
          onMenuUnmount={this.reloadList}
        />}

        {filters &&
        <FilteringMenuContainer filters={filters}
          currentParams={this.state.params}
          setParam={this.setParam}
          unsetParam={this.unsetParam}
          onMenuUnmount={this.reloadList}
        />}

        {view ? <ViewMenuContainer {...view} onViewFieldsMenuUnmount={view.onViewFieldsMenuUnmount} /> : null}
      </ul>
    );
  }
}
