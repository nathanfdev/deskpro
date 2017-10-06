import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import ListHeader from './ListHeader';
import List from './List';
import Edit from './Edit';

@connect(state => ({
  customReports:        state.Application.reports.get('customReports'),
  builtInReports:       state.Application.reports.get('builtInReports'),
  customReportsLoaded:  state.Application.reports.get('customReportsLoaded'),
  builtInReportsLoaded: state.Application.reports.get('builtInReportsLoaded'),
}))
class Wrapper extends React.Component {

  static propTypes = {
    customReports:        PropTypes.object,
    builtInReports:       PropTypes.object,
    customReportsLoaded:  PropTypes.bool,
    builtInReportsLoaded: PropTypes.bool,
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

  onReportClick(report) {
    this.setState({ currentReport: report });
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
        <Edit report={this.state.currentReport} />
      </span>
    );
  }
}

export default Wrapper;
