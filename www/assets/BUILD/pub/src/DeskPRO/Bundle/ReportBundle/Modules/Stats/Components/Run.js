import React, { PropTypes } from 'react';
import AmCharts from '@amcharts/amcharts3-react';
import { Loader } from 'deskpro-components';

class Run extends React.Component {

  static propTypes = {
    report:        PropTypes.object,
    reportLoading: PropTypes.bool.isRequired,
  };

  renderRun() {
    const { report } = this.props;
    const options = report.get('rendered_result') ? report.get('rendered_result').toJS() : {};

    return report.get('rendered_result')
      ? <AmCharts.React style={{ width: '100%', height: '500px' }} options={options} />
      : <span>No results found. Please try another query (e.g. change vars) to find something</span>;
  }

  render() {
    return (
      <div className="report-widget-edit-n-run-content">
        {this.props.reportLoading ? <Loader size="xlarge" /> : this.renderRun()}
      </div>
    );
  }
}

export default Run;
