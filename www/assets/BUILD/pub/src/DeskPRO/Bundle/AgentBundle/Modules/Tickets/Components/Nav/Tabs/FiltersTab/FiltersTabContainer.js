import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { filterSetsCountSelector } from '../../../../Selectors/nav';
import { FiltersTab } from './FiltersTab';

@connect(state => ({
  filterSetsCount: filterSetsCountSelector(state)
}))
export class FiltersTabContainer extends Component {
  render() {
    return <FiltersTab {...this.props} />;
  }
}
