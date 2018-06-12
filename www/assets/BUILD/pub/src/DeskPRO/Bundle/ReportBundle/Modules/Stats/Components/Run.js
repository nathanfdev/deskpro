import PropTypes from 'prop-types';
import React from 'react';
import AmCharts from '@amcharts/amcharts3-react';
import { Button, Loader } from '@deskpro/react-components';
import Select from 'react-select';
import Immutable from 'immutable';
import Handlebars from 'handlebars';
import TitleWithVars from './TitleWithVars';
import { displayTypes } from './helper';
import DataTable from './DataTables';
import SimpleStat from './SimpleStat';

class Run extends React.Component {

  static propTypes = {
    report:                     PropTypes.object.isRequired,
    reportLoading:              PropTypes.bool.isRequired,
    onChangeReportDisplayTypes: PropTypes.func.isRequired,
    onChangeReportVar:          PropTypes.func.isRequired,
    onRunClick:                 PropTypes.func.isRequired,
    onDeleteClick:              PropTypes.func.isRequired,
    onEditReportClick:          PropTypes.func.isRequired,
    onDownloadClick:            PropTypes.func.isRequired,
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
      ? Run.doRenderChart(options, index)
      : <span dangerouslySetInnerHTML={{ __html: options }} />;
  }

  static doRenderChart(options, index) {
    const newOptions = options.toJS();

    if (newOptions.valueAxes) {
      if (newOptions.valueAxes[0] && (newOptions.valueAxes[0].hash || newOptions.valueAxes[0].labelTemplate)) {
        newOptions.valueAxes[0].labelFunction = (value) => {
          const hash = newOptions.valueAxes[0].hash;
          let finalValue = value;

          if (hash) {
            finalValue = hash[value] ? hash[value] : '';
          }
          if (newOptions.valueAxes[0].labelTemplate) {
            const template = Handlebars.compile(newOptions.valueAxes[0].labelTemplate);
            finalValue = template({ value: finalValue });
          }

          return finalValue;
        };
      }
      if (newOptions.valueAxes[1] && newOptions.valueAxes[1].hash) {
        newOptions.valueAxes[1].labelFunction = (value) => {
          const hash = newOptions.valueAxes[1].hash;
          return hash[value] ? hash[value] : '';
        };
      }
    }

    if (newOptions.categoryAxis && newOptions.categoryAxis.labelTemplate) {
      newOptions.categoryAxis.labelFunction = value =>
        Handlebars.compile(newOptions.categoryAxis.labelTemplate)({ category: value });
    }

    if (newOptions.graphs) {
      newOptions.graphs = newOptions.graphs.map((g) => {
        if (g.balloonTextTemplate) {
          g.balloonFunction = (item, graph) => {
            const vars = { item, graph };
            Object.keys(item.dataContext).forEach((k) => {
              vars[k] = item.dataContext[k];
            });
            return Handlebars.compile(g.balloonTextTemplate)(vars);
          };
        }
        return g;
      });
    }

    switch (newOptions.chartType) {
      case 'pie':
      case 'bar':
      case 'line':
      case 'gauge':
      case 'area':
      case 'bubble':
        return <AmCharts.React key={index} style={{ width: '100%', height: '500px' }} options={newOptions} />;
      case 'table':
        return (<DataTable
          key={index}
          style={{ width: '100%', height: '500px' }}
          data={options.get('data').toJS()}
          columns={options.get('columns').toJS()}
          options={{
            scrollCollapse: true,
            autoWidth:      true,
            pageResize:     false,
            pageLength:     50,
            ordering:       true,
            order:          [],
            searching:      false,
            lengthChange:   true,
            height:         null
          }}
        />);
      case 'stat':
        return <SimpleStat value={options.get('value')} description={options.get('description')} />;
      default:
        return null;
    }
  }

  constructor(props) {
    super(props);
    this.state = {
      displayTypes: props.report.get('display_types', Immutable.List()).toJS()
    };
  }

  componentWillReceiveProps(props) {
    this.setState({ displayTypes: props.report.get('display_types', Immutable.List()).toJS() });
  }

  onChangeReportDisplayTypes = (runDisplayTypes) => {
    const types = runDisplayTypes ? runDisplayTypes.map(v => v.value) : [];
    this.setState({ displayTypes: types }, () => this.props.onChangeReportDisplayTypes(types));
  };

  onEditClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onEditReportClick(this.props.report);
  };

  onRunClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onRunClick(this.props.report);
  };

  onDownloadClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onDownloadClick(this.props.report, 'csv');
  };

  onDeleteClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onDeleteClick(this.props.report);
  };

  clickSlice = (event) => {
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
  };

  renderReport() {
    const { report } = this.props;
    const results = report.get('rendered_result') ? report.get('rendered_result') : Immutable.List();
    return results.map((renderedResult, index) => {
      if (renderedResult.get('title')) {
        return (
          <div>
            {this.renderDownload()}
            <b>{renderedResult.get('title')}</b>
            {Run.renderChart(renderedResult, index)}
          </div>
        );
      }

      return [this.renderDownload(), Run.renderChart(renderedResult, index)];
    });
  }

  renderDownload() {
    return (<div className="download_link">
      <span onClick={this.onDownloadClick}><i className="fa fa-file-excel-o" />Download as CSV</span>
    </div>);
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
            {report.get('is_custom') && <Button size="medium" onClick={this.onDeleteClick}>Delete</Button>}
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
