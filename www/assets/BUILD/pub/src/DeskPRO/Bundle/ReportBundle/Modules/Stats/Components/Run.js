import React, { PropTypes } from 'react';
import AmCharts from '@amcharts/amcharts3-react';
import { Loader } from '@deskpro/react-components';
import { Select } from 'DeskPRO/Component/Semantic/ReactForm';
import { displayTypes } from './helper';
import Header from '../../../../../Component/Semantic/Common/Header';
import { Form, Field } from '../../../../../Component/Semantic/ReactForm/Field';

class Run extends React.Component {

  static propTypes = {
    report:        PropTypes.object,
    reportLoading: PropTypes.bool.isRequired,
    runReport:     PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      runAs: 'pie'
    };
    this.onChange = this.onChange.bind(this);
  }

  onChange(runAs) {
    this.setState({ runAs }, () => this.props.runReport(runAs));
  }

  renderReport() {
    const { report } = this.props;
    const options = report.get('rendered_result') ? report.get('rendered_result').toJS() : {};

    return (
      <div className="ui form">
        <Header content={report.get('title')} level={2} />
        <div className="inline fields">
          <div className="eight wide field">
            <label htmlFor="runAs">Run this report as</label>
            <Select style={{ minWidth: '150px' }} id="runAs" value={this.state.runAs} onChange={this.onChange} choices={displayTypes} />
          </div>
        </div>
        <AmCharts.React style={{ width: '100%', height: '500px' }} options={options} />
      </div>
    );
  }

  renderRun() {
    const { report } = this.props;

    return report.get('rendered_result')
      ? this.renderReport()
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
