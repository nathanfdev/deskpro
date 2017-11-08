import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { saveAs } from 'file-saver';
import json2csv from 'json2csv';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export class SaveAsCsv extends Component {
  static propTypes = {
    content:           PropTypes.string.isRequired,
    exportedFields:    PropTypes.array.isRequired,
    currentListParams: PropTypes.object.isRequired,
    count:             PropTypes.number
  };

  saveAsCsv = (event) => {
    event.preventDefault();
    const { exportedFields, currentListParams, content, count = 200 } = this.props;

    repository(content).loadCsv(Object.assign({}, currentListParams, { count }))
      .then(response => {
        const fields     = [];
        const fieldNames = [];
        exportedFields.map(field => {
          if (field.get('visible')) {
            fields.push(field.get('id'));
            fieldNames.push(field.get('title'));
          }
          return null;
        });
        json2csv({ data: response.getData().data, fields, fieldNames }, (err, csv) => {
          if (err) console.log(err);
          const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
          saveAs(blob, `${content}_list.csv`);
        });
      });
  };

  render() {
    return (
      <a href="#" className="dpw--panel-button" onClick={this.saveAsCsv}>Save as CSV</a>
    );
  }
}
