import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { getImportStatus, stopImport } from '../../../Actions/importerActions';
import ImporterStatus from './ImporterStatus';
import { importerSources } from '../ImporterContainer';
import { replaceRoute } from '../../../../../Services/history';

@connect()
class ImporterStatusContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      status: null
    };
  }

  componentDidMount() {
    this.mounted = true;
    this.pollingRequest();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  pollingRequest = () => {
    const { dispatch } = this.props;
    const { status } = this.state;

    const onErrorResponse = () => replaceRoute('/apps/importer');
    const onSuccessResponse = ({ data }) => {
      this.setState({
        status: data.data
      }, () => setTimeout(this.pollingRequest, 5000));
    };

    const promise = dispatch(getImportStatus(status && status.id));
    promise.then(onSuccessResponse, onErrorResponse);
  };

  stopImport = () => {
    this.props.dispatch(stopImport());
  };

  render() {
    const { status } = this.state;
    if (!status) {
      return <LoadingPage />;
    }

    const source = importerSources[status.source_type];

    return (
      <ImporterStatus
        {...this.props}
        {...this.state}
        title={source.title}
        steps={source.steps}
        importedSteps={status.imported_steps}
        importedCounts={status.imported_counts}
        appliedSteps={status.applied_steps}
        appliedCounts={status.applied_counts}
        status={status.status}
        log={status.log}
        stopImport={this.stopImport}
      />
    );
  }
}

export default ImporterStatusContainer;
