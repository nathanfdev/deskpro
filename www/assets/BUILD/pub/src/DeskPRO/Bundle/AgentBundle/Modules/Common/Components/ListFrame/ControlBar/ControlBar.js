import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { SortingMenu } from './Sorting/SortingMenu';
import { FilteringMenuContainer } from './Filtering/FilteringMenuContainer';
import { ViewMenuContainer } from './View/ViewMenuContainer';

@connect()
export class ControlBar extends Component {

  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
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
        label: PropTypes.string.isRequired,
        icon:  PropTypes.string.isRequired,

        configurableFields:    PropTypes.object.isRequired,
        visibleFields:         PropTypes.oneOfType([PropTypes.array, PropTypes.object]),
        toggleFieldVisibility: PropTypes.func.isRequired
      })).isRequired,

      viewMode:                PropTypes.string.isRequired,
      viewModeAction:          PropTypes.func.isRequired,
      onViewFieldsMenuUnmount: PropTypes.func.isRequired
    })
  };

  constructor(props) {
    super(props);
    this.state = {
      changed: false,
      params:  {}
    };
  }

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

  setParam = (param) => {
    const newParams = this.state.params;
    newParams[param.param] = param.value;

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
    const { dispatch, applyParams } = this.props;
    if (this.state.changed) {
      dispatch(applyParams(this.state.params));
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
        <SortingMenu
          options={sorting}
          currentParams={this.state.params}
          setParam={this.setParam}
          onMenuUnmount={this.reloadList} />}

        {filters &&
        <FilteringMenuContainer
          filters={filters}
          currentParams={this.state.params}
          setParam={this.setParam}
          unsetParam={this.unsetParam}
          onMenuUnmount={this.reloadList} />}

        {view && <ViewMenuContainer {...view} onViewFieldsMenuUnmount={view.onViewFieldsMenuUnmount} />}
      </ul>
    );
  }
}
