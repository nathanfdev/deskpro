import React from 'react';
import PropTypes from 'prop-types';
import SectionHeader from '../../../Common/Components/SectionHeader';
import { replaceRoute } from '../../../../Services/history';
import { importerSources, stepTitles } from './ImporterContainer';

class Importer extends React.Component {

  static propTypes = {
    logs:    PropTypes.array,
    sources: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedType: null
    };
  }

  onSelect = (selectedType) => {
    this.setState({ selectedType });
  };

  onClickNext = (event) => {
    event.preventDefault();
    const { selectedType } = this.state;
    if (!selectedType) {
      return;
    }

    replaceRoute(`/apps/importer/source/${selectedType}`);
  };

  render() {
    const { sources, logs } = this.props;
    const { selectedType } = this.state;

    return (
      <div className="page admin-importer">
        <SectionHeader
          title="Data Importer"
          description="The importer allows you to import data from other sources into Deskpro.
          To begin select a data source."
          dividing
        />

        <div className="admin-importer-list">
          {Object.keys(sources).map(type =>
            <ImporterItem
              params={sources[type]}
              type={type}
              onSelect={this.onSelect}
              selected={selectedType === type}
            />
          )}
        </div>

        <button className="ui button" onClick={this.onClickNext}>
          Next
        </button>

        {logs.size > 0 &&
        <table className="table admin-importer-log-list">
          <thead>
            <th>Source Type</th>
            <th>Date Created</th>
            <th>Imported objects</th>
            <th>Download log</th>
          </thead>
          <tbody>
            {logs.toArray().map((log) => {
              const source = importerSources[log.get('type')];

              return (
                <tr>
                  <td>{log.get('type')}</td>
                  <td>{log.get('date_created')}</td>
                  <td>
                    {source.steps.map(step =>
                      <div>
                        {stepTitles[step]} ({log.getIn(['counts', step])})
                      </div>
                    )}
                  </td>
                  <td>
                    {log.get('log_blobs').toArray().map(blob =>
                      <div>
                        <a href={`${blob.get('download_url')}?dl=1`}>
                          {blob.get('filename')} ({blob.get('filesize_readable')})
                        </a>
                      </div>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>}
      </div>
    );
  }
}

class ImporterItem extends React.Component {

  static propTypes = {
    type:     PropTypes.string,
    params:   PropTypes.object,
    onSelect: PropTypes.func,
    selected: PropTypes.bool
  };

  render() {
    const { type, params, onSelect, selected } = this.props;

    return (
      <div
        className="admin-importer-list-item"
        onClick={() => onSelect(type)}
      >
        <div className="admin-importer-list-item-input">
          <input type="radio" name="type" checked={selected} />
        </div>
        <div className="admin-importer-list-item-title">
          <div>{params.title}</div>
          <div>{params.description}</div>
        </div>
      </div>
    );
  }
}

export default Importer;
