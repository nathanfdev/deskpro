import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import ListHeader from './ListHeader';
import List from './List';
import Edit from './Edit';
import { loadReport } from '../../Application/Actions/reportActions';

@connect(state => ({
  customReports:        state.Application.reports.get('customReports'),
  builtInReports:       state.Application.reports.get('builtInReports'),
  customReportsLoaded:  state.Application.reports.get('customReportsLoaded'),
  builtInReportsLoaded: state.Application.reports.get('builtInReportsLoaded'),
  currentReport:        state.Application.reports.get('currentReport'),
}))
class Wrapper extends React.Component {

  static propTypes = {
    customReports:        PropTypes.object,
    builtInReports:       PropTypes.object,
    customReportsLoaded:  PropTypes.bool,
    builtInReportsLoaded: PropTypes.bool,
    currentReport:        PropTypes.object,
    dispatch:             PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      currentReport: Immutable.fromJS({}),
      searchText:    '',
      activeLabels:  []
    };

    this.onReportClick = this.onReportClick.bind(this);
  }

  componentWillReceiveProps(props) {
    if (props.currentReport && this.props.currentReport.get('id') !== props.currentReport.get('id')) {
      this.setState({ currentReport: props.currentReport });
    }
  }

  onReportClick(report) {
    this.props.dispatch(loadReport(report.get('id')));
  }

  render() {
    const { customReports, builtInReports, customReportsLoaded, builtInReportsLoaded } = this.props;

    return (
      <span>
        <div className="report-list-wrapper">
          <div className="report-list-content">
            <div className="reports-list">
              <ListHeader />
              <List
                customReports={customReports}
                builtInReports={builtInReports}
                customReportsLoaded={customReportsLoaded}
                builtInReportsLoaded={builtInReportsLoaded}
                onReportClick={this.onReportClick}
              />
            </div>
          </div>
        </div>
        { this.props.currentReport.get('query_parts') ? <Edit report={this.state.currentReport} /> : null }
      </span>
    );
  }
}

export default Wrapper;
