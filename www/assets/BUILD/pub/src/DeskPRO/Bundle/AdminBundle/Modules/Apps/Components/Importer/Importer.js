import React from 'react';
import PropTypes from 'prop-types';
import SectionHeader from '../../../Common/Components/SectionHeader';
import { replaceRoute } from '../../../../Services/history';

class Importer extends React.Component {

  static propTypes = {
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
    const { sources } = this.props;
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
