import PropTypes from 'prop-types';
import React from 'react';
import AmCharts from '@amcharts/amcharts3-react';
import Button from '@deskpro/react-components/lib/Components/Buttons/Button';
import { Loader } from '@deskpro/react-components';
import Select from 'react-select';
import Immutable from 'immutable';
import TitleWithVars from './TitleWithVars';
import { displayTypes } from './helper';

class Run extends React.Component {

  static propTypes = {
    report:                     PropTypes.object.isRequired,
    reportLoading:              PropTypes.bool.isRequired,
    onChangeReportDisplayTypes: PropTypes.func.isRequired,
    onChangeReportVar:          PropTypes.func.isRequired,
    onRunClick:                 PropTypes.func.isRequired,
    onEditReportClick:          PropTypes.func.isRequired,
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
    this.onEditClick                = this.onEditClick.bind(this);
    this.onRunClick                 = this.onRunClick.bind(this);
  }

  componentWillReceiveProps(props) {
    this.setState({ displayTypes: props.report.get('display_types', Immutable.List()).toJS() });
  }

  onChangeReportDisplayTypes(runDisplayTypes) {
    const types = runDisplayTypes ? runDisplayTypes.map(v => v.value) : [];
    this.setState({ displayTypes: types }, () => this.props.onChangeReportDisplayTypes(types));
  }

  onEditClick(event) {
    event.preventDefault();
    event.stopPropagation();
    this.props.onEditReportClick(this.props.report);
  }

  onRunClick(event) {
    event.preventDefault();
    event.stopPropagation();
    this.props.onRunClick(this.props.report);
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
    const content = report.get('rendered_result', Immutable.List()).filter(value => value).size > 0
      ? <div className="results-wrap">{this.renderReport()}</div>
      : <div className="no-results">No results found.</div>;

    const choices = displayTypes;

    return (
      <div className="report-view run">
        <div className="title-bar">
          <div className="title">{title}</div>
          <div className="ctrl">
            <Button size="medium" type="secondary" onClick={this.onRunClick}><i className="fa fa-refresh" /></Button>
            <Button size="medium" onClick={this.onEditClick}>Edit Report</Button>
          </div>
        </div>
        <div className="display-as-option">
          <label htmlFor="displayTypes">Display</label>
          <Select
            multi
            closeOnSelect={false}
            options={choices}
            value={this.state.displayTypes}
            onChange={this.onChangeReportDisplayTypes}
          />
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
