import React, { Component, PropTypes } from 'react';
import { saveAs } from 'file-saver';
import json2csv from 'json2csv';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { List } from './List';
import { currentViewModeSelector, paginationSelector, listParamsSelector, fieldsSelector } from '../../Selectors/list';
import {
  isLoadedCollectionSelectorFactory, releaseCollection, setCollection
}
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { applyListParams } from '../../Actions/listActions';

import { connect } from 'react-redux';
@connect(state => ({
  isLoaded:          isLoadedCollectionSelectorFactory('Person', 'tickets')(state),
  currentListParams: listParamsSelector(state),
  selected:          selectedSelector(state),
  pagination:        paginationSelector(state),
  fieldsConfig:      fieldsSelector(state),
  viewMode:          currentViewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    currentListParams: PropTypes.object.isRequired,
    fieldsConfig:      PropTypes.object.isRequired,
    selected:          PropTypes.object.isRequired,
    viewMode:          PropTypes.string.isRequired,
    pagination:        PropTypes.object
  };

  componentDidMount() {
    this.props.dispatch(setCollection('Ticket', 'list', []));
  }

  componentWillUnmount() {
    this.props.dispatch(releaseCollection('Ticket', 'list'));
  }

  saveAsCsv = (event) => {
    event.preventDefault();
    const { currentListParams, fieldsConfig, viewMode } = this.props;
    repository('Ticket').loadCsv(Object.assign({}, currentListParams.toJS(), { count: 1000 }))
      .then(response => {
        const visibleFields = fieldsConfig.get(viewMode).toArray();
        const fields     = [];
        const fieldNames = [];
        visibleFields.map(item => {
          fields.push(item.get('id'));
          fieldNames.push(item.get('title'));
          return null;
        });
        json2csv({ data: response.getData().data, fields, fieldNames }, (err, csv) => {
          if (err) console.log(err);
          const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
          saveAs(blob, 'ticket_list.csv');
        });
      });
  };

  render() {
    const handlePageClick = page => {
      this.props.dispatch(applyListParams({ page }));
    };

    return <List {...this.props} handlePageClick={handlePageClick} saveAsCsv={this.saveAsCsv} />;
  }
}
