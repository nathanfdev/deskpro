import PropTypes from 'prop-types';
import React from 'react';
import AmCharts from '@amcharts/amcharts3-react';
import { Loader } from '@deskpro/react-components';
import { MultiSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import Immutable from 'immutable';
import TitleWithVars from './TitleWithVars';
import { displayTypes } from './helper';
import Header from '../../../../../Component/Semantic/Common/Header';

class Run extends React.Component {

  static propTypes = {
    report:                     PropTypes.object,
    reportLoading:              PropTypes.bool.isRequired,
    onChangeReportDisplayTypes: PropTypes.func.isRequired,
    onChangeReportVar:          PropTypes.func.isRequired,
    groupParams:                PropTypes.object.isRequired,
  };

  static renderChart(renderedResult, index) {
    let options = renderedResult;
    if (!options) {
      return null;
    }
    if (typeof options === 'object') {
      options = options.set('listeners', [{
        event:  'clickSlice',
        method: this.clickSlice
      }]);
    }

    return typeof options === 'object'
      ? <AmCharts.React key={index} style={{ width: '100%', height: '500px' }} options={options.toJS()} />
      : <span dangerouslySetInnerHTML={{ __html: options }} />;
  }

  constructor(props) {
    super(props);
    this.state = {
      displayTypes: props.report.get('display_types', Immutable.List()).toJS()
    };
    this.onChangeReportDisplayTypes = this.onChangeReportDisplayTypes.bind(this);
    this.clickSlice                 = this.clickSlice.bind(this);
  }

  componentWillReceiveProps(props) {
    this.setState({ displayTypes: props.report.get('display_types', Immutable.List()).toJS() });
  }

  onChangeReportDisplayTypes(runDisplayTypes) {
    this.setState({ displayTypes: runDisplayTypes }, () => this.props.onChangeReportDisplayTypes(runDisplayTypes));
  }

  clickSlice(event) {
    const options = this.props.report.get('rendered_result').toJS();
    let selected;
    if (event.dataItem.dataContext.id) {
      selected = event.dataItem.dataContext.id;
    }
    const chart = event.chart;
    if (selected) {
      const data = [];
      options.dataProvider.forEach((element, index) => {
        if (index === selected) {
          options.pies[selected].dataProvider.forEach((pie) => {
            pie.color = `#${Math.floor(Math.random() * 16777215).toString(16)}`;
            data.push(pie);
          });
        } else {
          data.push(element);
        }
      });
      chart.dataProvider = data;
    } else {
      chart.dataProvider = options.dataProvider;
    }
    chart.validateData();
  }

  renderReport() {
    const { report } = this.props;
    const results = report.get('rendered_result') ? report.get('rendered_result') : Immutable.List();
    return results.map((renderedResult, index) => Run.renderChart(renderedResult, index));
  }

  renderRun() {
    const { report, onChangeReportVar, groupParams } = this.props;

    const title = (<TitleWithVars
      onChangeReportVar={onChangeReportVar}
      groupParams={groupParams}
      report={report}
    />);
    const content = report.get('rendered_result').filter(value => value).size > 0
      ? this.renderReport()
      : <span>No results found. Please try another query (e.g. change vars) to find something</span>;

    const choices = displayTypes.map((value) => {
      const newValue = value;
      newValue.disabled = !report.get('is_custom');
      return newValue;
    });

    return (
      <div className="ui form">
        <Header content={title} level={3} />
        <div className="inline fields">
          <div className="eight wide field">
            <label htmlFor="runAs">Run this report as</label>
            <MultiSelect
              toggleAll={false}
              choices={choices}
              value={this.state.displayTypes}
              onChange={this.onChangeReportDisplayTypes}
            />
          </div>
        </div>
        { content }
      </div>
    );
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
