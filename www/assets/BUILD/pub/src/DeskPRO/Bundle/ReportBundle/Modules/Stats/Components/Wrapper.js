import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import ListHeader from './List/ListHeader';
import List from './List/List';
import { Edit } from './Edit';
import Run from './Run';
import {
  loadReport,
  runReport,
  saveAndRun,
  parseQuery,
  deleteReport,
  downloadReport
} from '../../Application/Actions/reportActions';
import { allReportsSelector } from '../Selectors/reports';
import { allReportLabelsSelector } from '../../Application/Selectors/reports';
import { regex, activateLabel, transformLabels, transformReportData, countActiveLabels } from './helper';

@connect(state => ({
  reports:       allReportsSelector(state),
  reportsLoaded: state.Application.reports.get('reportsLoaded'),
  groupParams:   state.Application.reports.get('groupParams'),
  labels:        allReportLabelsSelector(state)
}))
class Wrapper extends React.Component {

  static defaultProps = {
    reportsLoaded: false,
    labels:        null
  };

  static propTypes = {
    reports:       PropTypes.object.isRequired,
    reportsLoaded: PropTypes.bool,
    groupParams:   PropTypes.object.isRequired,
    labels:        PropTypes.object,
    dispatch:      PropTypes.func.isRequired,
  };

  static createNewReport(report) {
    let toClone = report;
    if (!report) {
      toClone = {};
    }

    return Immutable.fromJS({
      id:            0,
      unique_key:    '',
      title:         '',
      description:   '',
      query:         toClone.query || '',
      labels:        [],
      display_order: 10,
      display_types: [],
      variables:     toClone.variables || [],
      query_parts:   toClone.query_parts || {
        select:      '',
        from:        '',
        where:       '',
        split_by:    '',
        group_by:    '',
        order_by:    '',
        with_rollup: false,
        limit:       '',
        offset:      ''
      },
      is_custom: true,
      is_new:    true
    });
  }

  constructor(props) {
    super(props);

    let newLabels = Immutable.List();
    if (props.labels && props.labels.size > 0) {
      newLabels = transformLabels(props.labels);
    }

    this.state = {
      currentReport: Immutable.Map(),
      currentErrors: Immutable.Map(),
      reportLoading: false,
      reports:       props.reports || Immutable.Map(),
      searchText:    '',
      labels:        newLabels,
      activeLabels:  0,
    };

    this.onEditReportClick          = this.onEditReportClick.bind(this);
    this.onDownloadReportClick      = this.onDownloadReportClick.bind(this);
    this.onRunReportClick           = this.onRunReportClick.bind(this);
    this.onDeleteReportClick        = this.onDeleteReportClick.bind(this);
    this.onCloneReportClick         = this.onCloneReportClick.bind(this);
    this.onChangeReportDisplayTypes = this.onChangeReportDisplayTypes.bind(this);
    this.onChangeFilterText         = this.onChangeFilterText.bind(this);
    this.onChangeReportVar          = this.onChangeReportVar.bind(this);
    this.onLabelClick               = this.onLabelClick.bind(this);
    this.onSubmit                   = this.onSubmit.bind(this);
    this.onAddClick                 = this.onAddClick.bind(this);
    this.filter                     = this.filter.bind(this);
    this.parseReportQuery           = this.parseReportQuery.bind(this);
  }

  componentWillReceiveProps(props) {
    let newReports = props.reports;
    this.state.reports.forEach((report) => { // we have to persist changed var values, to keep run mode work
      if (report.get('varChanged')) {
        newReports = newReports.mergeIn([report.get('id')], { variables: report.get('variables'), varChanged: true });
      }
    });
    this.setState({ reports: newReports });
    this.setLabels(props);
  }

  onChangeReportVar(report, varName, value) {
    let changedReport = report;
    changedReport.get('variables').forEach((val, index) => {
      if (val.get('name') === varName) {
        changedReport = changedReport.setIn(['variables', index, 'value'], value).set('varChanged', true);
      }
    });
    const newReports = this.state.reports.set(report.get('id'), changedReport);
    this.setState({ currentReport: report, reports: newReports }, () => this.onRunReportClick(changedReport));
  }

  onDownloadReportClick(report, type) {
    this.props.dispatch(downloadReport(report.get('id'), transformReportData(report), type));
  }

  onEditReportClick(report) {
    this.setState({
      currentReport: Immutable.Map(),
      reportLoading: true,
    });

    const promise = this.props.dispatch(loadReport(report.get('id')));
    promise.then((response) => {
      this.setState({
        reportLoading: false,
        currentReport: Immutable.fromJS(response.data.data)
      });
    }, () => {
      this.setState({
        reportLoading: false
      });
    });

    this.setState({ mode: 'edit' });
  }

  onDeleteReportClick(report) {
    this.props.dispatch(deleteReport(report.get('id'))).then(() => {
      this.setState({
        mode: null
      });
    });
  }

  onRunReportClick(report) {
    this.setState({
      currentErrors: Immutable.Map(),
      currentReport: Immutable.Map(),
      reportLoading: true,
    });

    const promise = this.props.dispatch(runReport(report.get('id'), transformReportData(report)));
    promise.then((response) => {
      const reportData = report.set('rendered_result', Immutable.fromJS(response.data.data.rendered_result));

      this.setState({
        reportLoading: false,
        currentReport: reportData
      });
    }, (response) => {
      this.setState({
        currentReport: report,
        currentErrors: Immutable.fromJS(response.data),
        reportLoading: false
      });
    });

    this.setState({ mode: 'run' });
  }

  onCloneReportClick(report) {
    this.setState({
      currentReport: Wrapper.createNewReport(report.toJS())
    });
  }

  onChangeReportDisplayTypes(displayTypes) {
    const { currentReport } = this.state;
    const { dispatch } = this.props;
    const newCurrentReport = currentReport.set('display_types', Immutable.List(displayTypes));
    this.setState({ mode: 'run', currentReport: newCurrentReport });
    const data = transformReportData(newCurrentReport);
    data.displayOnly = true;
    dispatch(saveAndRun(data));
  }

  onChangeFilterText(value) {
    let labels = this.state.labels;
    let activeLabels = this.state.activeLabels;
    let matches = regex.exec(value);
    let state = this.state;
    state.searchText = value;
    state.labels = labels.map(label => label.set('active', false));
    state.activeLabels = 0;

    while (matches !== null) {
      const result = activateLabel(matches[1], labels, false);
      labels = result.newLabels;
      activeLabels = result.newActiveLabels;
      matches = regex.exec(value);
      state = { labels, activeLabels, searchText: value };
    }

    this.setState(state);
  }

  onAddClick(cloneReportObj) {
    this.setState({
      mode:          'edit',
      currentReport: Wrapper.createNewReport(cloneReportObj)
    });
  }

  onLabelClick(clickedLabel) {
    const { newLabels, newActiveLabels } = activateLabel(clickedLabel, this.state.labels);
    let newSearchText = this.state.searchText.replace(regex, '');

    const labels = newLabels
      .filter(value => value.get('active'))
      .map(value => `[${value.get('label')}]`)
      .toList().toJS();

    newSearchText = `${labels.join('')} ${newSearchText.trim()}`;

    this.setState({ labels: newLabels, activeLabels: newActiveLabels, searchText: newSearchText });
  }

  onSubmit(data) {
    const report = Immutable.fromJS(data);
    this.setState({ currentReport: report, reportLoading: true }, () => this.onRunReportClick(report));
  }

  setLabels(props) {
    if (props.labels) {
      let newLabels = transformLabels(props.labels);
      this.state.labels.forEach((label) => {
        const entry = newLabels.findEntry(newLabel => newLabel.get('label') === label.get('label'));
        if (entry && entry[1] && label.get('active')) {
          newLabels = newLabels.setIn([entry[0], 'active'], true);
        }
      });
      this.setState({ labels: newLabels, activeLabels: countActiveLabels(newLabels) });
    }
  }

  parseReportQuery(report, query) {
    this.props.dispatch(parseQuery(report, query));
  }

  filter(value) {
    let result = true;

    const { searchText } = this.state;

    const actualSearch = searchText.replace(regex, '').trim().toLowerCase();
    const activeLabels = [];

    let match;
    do {
      match = regex.exec(searchText);
      if (match) {
        activeLabels.push(match[1]);
      }
    } while (match);

    if (!actualSearch) {
      result = true;
    } else if (value.get('title').toLowerCase().indexOf(actualSearch) >= 0) {
      result = true;
    } else {
      result = value
        .get('labels')
        .reduce((reduced, label) => reduced || label.toLowerCase().indexOf(actualSearch) >= 0, false);
    }

    if (activeLabels.length > 0) {
      result = result && value
        .get('labels')
        .reduce((reduced, label) => reduced || activeLabels.indexOf(label) >= 0, false);
    }

    return result;
  }

  render() {
    const { reportsLoaded, groupParams } = this.props;
    const { reports, labels, activeLabels, searchText, currentReport, currentErrors, reportLoading, mode } = this.state;

    const filteredCustomReports = reports.filter(report => report.get('is_custom')).filter(this.filter);
    const filteredBuiltInReports = reports.filter(report => !report.get('is_custom')).filter(this.filter);

    return (
      <div className="stats-app-wrapper">
        <div className="report-list-wrapper">
          <div className="report-list-content">
            <div className="reports-list">
              <ListHeader
                onChange={this.onChangeFilterText}
                onLabelClick={this.onLabelClick}
                labels={labels}
                activeLabels={activeLabels}
                searchText={searchText}
                onAddClick={this.onAddClick}
              />
              <List
                customReports={filteredCustomReports}
                builtInReports={filteredBuiltInReports}
                currentReport={currentReport}
                groupParams={groupParams}
                labels={labels}
                reportsLoaded={reportsLoaded}
                onEditReportClick={this.onEditReportClick}
                onRunReportClick={this.onRunReportClick}
                onChangeReportVar={this.onChangeReportVar}
                onLabelClick={this.onLabelClick}
              />
            </div>
          </div>
        </div>
        <div className="report-list-pane-wrapper">
          { currentReport.get('query_parts') && mode === 'edit'
            ? <Edit
              parseQuery={this.parseReportQuery}
              reportLoading={reportLoading}
              labels={labels}
              report={currentReport}
              groupParams={groupParams}
              onSubmit={this.onSubmit}
              onRunClick={this.onRunReportClick}
              onCloneClick={this.onCloneReportClick}
            />
            : null
          }
          { currentReport && mode === 'run'
            ? <Run
              onChangeReportVar={this.onChangeReportVar}
              groupParams={groupParams}
              onChangeReportDisplayTypes={this.onChangeReportDisplayTypes}
              report={currentReport}
              reportErrors={currentErrors}
              reportLoading={reportLoading}
              onEditReportClick={this.onEditReportClick}
              onRunClick={this.onRunReportClick}
              onDownloadClick={this.onDownloadReportClick}
              onDeleteClick={this.onDeleteReportClick}
            />
            : null
          }
        </div>
      </div>
    );
  }
}

export default Wrapper;
