import React, { PropTypes } from 'react';
import { Loader } from '@deskpro/react-components';
import ListItem from './ListItem';

class List extends React.Component {

  static propTypes = {
    customReports:     PropTypes.object.isRequired,
    builtInReports:    PropTypes.object.isRequired,
    currentReport:     PropTypes.object,
    reportsLoaded:     PropTypes.bool.isRequired,
    onEditReportClick: PropTypes.func.isRequired,
    onRunReportClick:  PropTypes.func.isRequired,
    onLabelClick:      PropTypes.func.isRequired,
    labels:            PropTypes.object.isRequired
  };


  static showLoading() {
    return (
      <div style={{ textAlign: 'center', padding: '45px' }}>
        <Loader size="xlarge" />
      </div>
    );
  }

  showList() {
    const { onLabelClick, onEditReportClick, onRunReportClick, labels, currentReport } = this.props;
    return (
      <div className="stat-list-wrapper">
        <ul className="stat-list">
          {this.props.customReports.map(
            report =>
              <ListItem
                key={report.get('id')}
                onLabelClick={onLabelClick}
                onEditReportClick={onEditReportClick}
                onRunReportClick={onRunReportClick}
                isActive={currentReport && currentReport.get('id') === report.get('id')}
                report={report}
                labels={labels}
              />
          )}
          {this.props.builtInReports.map(
            report =>
              <ListItem
                key={report.get('id')}
                onLabelClick={onLabelClick}
                onEditReportClick={onEditReportClick}
                onRunReportClick={onRunReportClick}
                isActive={currentReport && currentReport.get('id') === report.get('id')}
                report={report}
                labels={labels}
              />
          )}
        </ul>
      </div>
    );
  }

  render() {
    return (
      <div className="big-list-of-stats">
        { this.props.reportsLoaded ? this.showList() : List.showLoading() }
      </div>
    );
  }
}

export default List;
