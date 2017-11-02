import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import ListHeader from './List/ListHeader';
import List from './List/List';
import Edit from './Edit';
import Run from './Run';
import { loadReport, saveReport, newReport, runReport } from '../../Application/Actions/reportActions';
import { allReportsSelector, allReportsLabelsSelector } from '../Selectors/reports';
import { regex, activateLabel, transformLabels, transformReportData } from './helper';

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
    reports:       PropTypes.object,
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
      searchText:    '',
      labels:        newLabels,
      activeLabels:  0
    };

    this.onEditReportClick  = this.onEditReportClick.bind(this);
    this.onRunReportClick   = this.onRunReportClick.bind(this);
    this.onRunReport        = this.onRunReport.bind(this);
    this.onChangeFilterText = this.onChangeFilterText.bind(this);
    this.onLabelClick       = this.onLabelClick.bind(this);
    this.onSubmit           = this.onSubmit.bind(this);
    this.onAddClick         = this.onAddClick.bind(this);
    this.filter             = this.filter.bind(this);
  }

  componentWillReceiveProps(props) {
    if (props.currentReport) {
      this.setState({ currentReport: props.currentReport });
    }
    this.setLabels(props);
  }

  onEditReportClick(report) {
    this.props.dispatch(loadReport(report.get('id')));
    this.setState({ mode: 'edit' });
  }

  onRunReportClick(report, data) {
    this.props.dispatch(runReport(report.get('id'), data));
    this.setState({ mode: 'run' });
  }

  onRunReport(displayType) {
    const { currentReport } = this.state;
    const data = transformReportData(currentReport);
    data.display_types = [displayType];
    this.props.dispatch(runReport(currentReport.get('id'), data));
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
    this.props.dispatch(newReport());
  }

  onLabelClick(clickedLabel) {
    const { newLabels, newActiveLabels } = activateLabel(clickedLabel, this.state.labels);
    let newSearchText = this.state.searchText.replace(regex, '');
    const labels = newLabels.filter(value => value.get('active')).map(value => `[${value.get('label')}]`).toList().toJS();
    newSearchText = `${labels.join('')} ${newSearchText.trim()}`;

    this.setState({ labels: newLabels, activeLabels: newActiveLabels, searchText: newSearchText });
  }

  onRunClick(report) {
    console.log(report, this.props.currentReport);
    console.log('open modal window to show report example');
  }

  onSubmit(data) {
    return this.props.dispatch(saveReport(data));
  }

  setLabels(props) {
    if (props.labels && this.state.labels.size < 1) {
      const newLabels = transformLabels(props.labels);
      this.setState({ labels: newLabels, activeLabels: 0 });
    }
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
    const { reports, reportsLoaded, groupParams, reportLoading } = this.props;
    const { labels, activeLabels, searchText, currentReport, mode } = this.state;

    const filteredCustomReports = reports.filter(report => report.get('is_custom')).filter(this.filter);
    const filteredBuiltInReports = reports.filter(report => !report.get('is_custom')).filter(this.filter);

    return (
      <span>
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
                reportsLoaded={reportsLoaded}
                onEditReportClick={this.onEditReportClick}
                onRunReportClick={this.onRunReportClick}
                onLabelClick={this.onLabelClick}
                labels={labels}
                groupParams={groupParams}
              />
            </div>
          </div>
        </div>
        { currentReport.get('query_parts') && mode === 'edit'
          ? <Edit
            reportLoading={reportLoading}
            labels={labels}
            report={currentReport}
            groupParams={groupParams}
            onSubmit={this.onSubmit}
            onRunClick={this.onRunClick}
          />
          : null
        }
        { currentReport && mode === 'run'
          ? <Run runReport={this.onRunReport} report={currentReport} reportLoading={reportLoading} />
          : null
        }
      </span>
    );
  }
}

export default Wrapper;
