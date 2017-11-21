import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import ListHeader from './List/ListHeader';
import List from './List/List';
import Edit from './Edit';
import Run from './Run';
import {
  loadReport,
  saveReport,
  newReport,
  runReport,
  saveAndRun,
  parseQuery,
  cloneReport
} from '../../Application/Actions/reportActions';
import { allReportsSelector, allReportsLabelsSelector } from '../Selectors/reports';
import { regex, activateLabel, transformLabels, transformReportData, countActiveLabels } from './helper';

@connect(state => ({
  reports:       allReportsSelector(state),
  reportsLoaded: state.Application.reports.get('reportsLoaded'),
  reportLoading: state.Application.reports.get('reportLoading'),
  currentReport: state.Application.reports.get('currentReport'),
  groupParams:   state.Application.reports.get('groupParams'),
  labels:        allReportsLabelsSelector(state),
}))
class Wrapper extends React.Component {

  static propTypes = {
    reports:       PropTypes.object.isRequired, // eslint-disable-line react/no-unused-prop-types
    reportsLoaded: PropTypes.bool,
    reportLoading: PropTypes.bool,
    currentReport: PropTypes.object,
    groupParams:   PropTypes.object,
    labels:        PropTypes.object,
    dispatch:      PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    let newLabels = Immutable.List();
    if (props.labels && props.labels.size > 0) {
      newLabels = transformLabels(props.labels);
    }

    this.state = {
      currentReport: Immutable.Map(),
      reports:       Immutable.List(),
      searchText:    '',
      labels:        newLabels,
      activeLabels:  0,
    };

    this.onEditReportClick          = this.onEditReportClick.bind(this);
    this.onRunReportClick           = this.onRunReportClick.bind(this);
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
    const { currentReport } = props;
    let newReports = props.reports;
    this.state.reports.forEach((report) => { // we have to persist changed var values, to keep run mode work
      if (report.get('varChanged')) {
        newReports = newReports.mergeIn([report.get('id')], { variables: report.get('variables'), varChanged: true });
      }
    });
    this.setState({ currentReport, reports: newReports });
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

  onEditReportClick(report) {
    this.props.dispatch(loadReport(report.get('id')));
    this.setState({ mode: 'edit' });
  }

  onRunReportClick(report) {
    this.props.dispatch(runReport(report.get('id'), transformReportData(report)));
    this.setState({ mode: 'run' });
  }

  onCloneReportClick(report) {
    this.props.dispatch(cloneReport(report.get('id')));
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

  onAddClick() {
    this.setState({ mode: 'edit' });
    this.props.dispatch(newReport());
  }

  onLabelClick(clickedLabel) {
    const { newLabels, newActiveLabels } = activateLabel(clickedLabel, this.state.labels);
    let newSearchText = this.state.searchText.replace(regex, '');
    const labels = newLabels.filter(value => value.get('active')).map(value => `[${value.get('label')}]`).toList().toJS();
    newSearchText = `${labels.join('')} ${newSearchText.trim()}`;

    this.setState({ labels: newLabels, activeLabels: newActiveLabels, searchText: newSearchText });
  }

  onSubmit(data) {
    return this.props.dispatch(saveReport(data));
  }

  setLabels(props) {
    if (props.labels) {
      let newLabels = transformLabels(props.labels);
      this.state.labels.forEach((label) => {
        const entry = newLabels.findEntry(newLabel => newLabel.get('value') === label.get('value'));
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

    const { activeLabels, labels, searchText } = this.state;

    const actualSearch = searchText.replace(regex, '').trim().toLowerCase();

    if (!actualSearch) {
      result = true;
    } else if (value.get('title').toLowerCase().indexOf(actualSearch) >= 0) {
      result = true;
    } else {
      result = value
        .get('labels')
        .reduce((reduced, label) => reduced || label.toLowerCase().indexOf(actualSearch) >= 0, false);
    }

    let labelsToCheck = [];
    if (activeLabels > 0) {
      labelsToCheck = labels.filter(label => label.get('active')).map(label => label.get('label')).toList().toJS();
      result = result && value
        .get('labels')
        .reduce((reduced, label) => reduced || labelsToCheck.indexOf(label) >= 0, false);
    }

    return result;
  }

  render() {
    const { reportsLoaded, groupParams, reportLoading } = this.props;
    const { reports, labels, activeLabels, searchText, currentReport, mode } = this.state;

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
              reportLoading={reportLoading}
              onEditReportClick={this.onEditReportClick}
              onRunClick={this.onRunReportClick}
            />
            : null
          }
        </div>
      </div>
    );
  }
}

export default Wrapper;
