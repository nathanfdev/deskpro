import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { startImport, testSettings } from '../../../Actions/importerActions';
import { replaceRoute } from '../../../../../Services/history';
import { importerSources } from '../ImporterContainer';

@connect()
class ImporterSourceContainer extends React.Component {

  static propTypes = {
    params:   PropTypes.object,
    dispatch: PropTypes.func
  };

  onTest = data => this.props.dispatch(testSettings(data));
  onSubmit = (data) => {
    const { dispatch } = this.props;
    const promise = dispatch(startImport(data));
    promise.success(() => {
      replaceRoute('/apps/importer/status');
    });

    return promise;
  };

  onReturnBack = () => {
    replaceRoute('/apps/importer');
  };

  render() {
    const { params } = this.props;
    const source = importerSources[params.type];
    if (!source) {
      return (
        <div>Importer source not found.</div>
      );
    }

    return React.createElement(source.component, {
      ...this.props,
      ...this.state,

      source,
      type:         params.type,
      onTest:       this.onTest,
      onSubmit:     this.onSubmit,
      onReturnBack: this.onReturnBack
    });
  }
}

export default ImporterSourceContainer;
