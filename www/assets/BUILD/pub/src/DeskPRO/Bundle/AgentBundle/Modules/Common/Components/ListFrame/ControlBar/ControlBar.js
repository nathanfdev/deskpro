import React, { Component, PropTypes } from 'react';
import { SortingMenu } from './Sorting/SortingMenu';
import { FilteringMenuContainer } from './Filtering/FilteringMenuContainer';
import { ViewMenuContainer } from './View/ViewMenuContainer';

import { connect } from 'react-redux';
@connect()
export class ControlBar extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    applyParams: PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired,
    sorting: PropTypes.shape({
      options: PropTypes.objectOf(PropTypes.shape({
        label: PropTypes.string.isRequired,
        icon: PropTypes.string.isRequired
      })),
      orderBy: PropTypes.string.isRequired,
      orderDir: PropTypes.string.isRequired,
      orderByAction: PropTypes.func.isRequired,
      orderDirAction: PropTypes.func.isRequired
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
          labels: PropTypes.object.isRequired
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
      ]))
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
      viewModeAction: PropTypes.func.isRequired,
      onViewFieldsMenuUnmount: PropTypes.func.isRequired
    })
  };

  componentWillMount() {
    this.setState({
      changed: false,
      params: this.props.currentParams.toJS()
    });
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      params: nextProps.currentParams.toJS()
    });
  }

  setParam = (params)=> {
    const newParams = this.state.params;
    newParams[params.param] = params.value;
    this.setState({
      changed: true,
      params: newParams
    });
  };

  unsetParam(param) {
    const newParams = this.state.params;
    delete newParams[param];
    this.setState({
      changed: true,
      params: newParams
    });
  }

  reloadList() {
    const { dispatch, applyParams } = this.props;
    if (this.state.changed) {
      console.log('state changed', this.state.params);
      dispatch(applyParams(this.state.params));
    }
  }

  render() {
    const { sorting, filtering, view } = this.props;
    console.log('state', this.state);

    return (
      <ul className="dpwd-navigation-dropdown-top-row-main-list">
        {sorting && <SortingMenu {...sorting} onMenuUnmount={this.reloadList.bind(this)}/>}

        {filtering &&
        <FilteringMenuContainer {...filtering} state={this.state}
                                               setParam={this.setParam.bind(this)}
                                               unsetParam={this.unsetParam.bind(this)}
                                               onMenuUnmount={this.reloadList.bind(this)}/>}

        {view && <ViewMenuContainer {...view} onViewFieldsMenuUnmount={view.onViewFieldsMenuUnmount}/>}
      </ul>
    );
  }
}

